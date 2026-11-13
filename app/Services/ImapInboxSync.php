<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Project;
use App\Models\Thread;
use App\Utils\ProjectSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\ClientManager;

/**
 * Fetch the project mailbox (IMAP) and store the emails sent by a prospect
 * as incoming messages, in the same tables as the sent ones: an incoming
 * email is a Message with from_user = 0 (see App\Services\IncomingCommunications).
 */
class ImapInboxSync
{
    /**
     * Project setting holding the email configuration (SMTP/Brevo and IMAP).
     */
    public const SETTING = 'email';

    public const DEFAULT_FOLDER = 'INBOX';

    /**
     * Upper bound of messages read in one run: a mailbox left alone for
     * months is imported over several runs instead of one long request.
     */
    public const MAX_PER_SYNC = 100;

    /**
     * Window of the first run, when there is no previous synchronisation.
     */
    public const DEFAULT_WINDOW_DAYS = 7;

    /**
     * Known providers: choosing one fills the server settings.
     */
    public const PROVIDERS = [
        'ovh' => ['name' => 'OVH', 'host' => 'imap.mail.ovh.net', 'port' => 993, 'encryption' => 'ssl'],
        'gmail' => ['name' => 'Gmail', 'host' => 'imap.gmail.com', 'port' => 993, 'encryption' => 'ssl'],
        'outlook' => ['name' => 'Outlook / Microsoft 365', 'host' => 'outlook.office365.com', 'port' => 993, 'encryption' => 'ssl'],
        'yahoo' => ['name' => 'Yahoo', 'host' => 'imap.mail.yahoo.com', 'port' => 993, 'encryption' => 'ssl'],
        'icloud' => ['name' => 'iCloud', 'host' => 'imap.mail.me.com', 'port' => 993, 'encryption' => 'ssl'],
        'zoho' => ['name' => 'Zoho', 'host' => 'imap.zoho.com', 'port' => 993, 'encryption' => 'ssl'],
        'infomaniak' => ['name' => 'Infomaniak', 'host' => 'mail.infomaniak.com', 'port' => 993, 'encryption' => 'ssl'],
    ];

    public static function providers(): array
    {
        return self::PROVIDERS;
    }

    /**
     * Open the mailbox and check the configured folder: no message is read.
     */
    public function test(Project $project): array
    {
        $config = $this->config($project);

        Log::info('IMAP: test de connexion', $this->logContext($project, $config) + [
            'folder' => $config['folder'],
        ]);

        try {
            $client = $this->connect($config);
        } catch (\Throwable $exception) {
            Log::error('IMAP: connexion impossible', $this->logContext($project, $config) + [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        try {
            $folder = $client->getFolder($config['folder']);

            if (!$folder) {
                throw new \RuntimeException("Le dossier {$config['folder']} est introuvable sur le serveur IMAP.");
            }

            Log::info('IMAP: connexion réussie', $this->logContext($project, $config) + [
                'folder' => $config['folder'],
            ]);

            return [
                'host' => $config['host'],
                'folder' => $config['folder'],
            ];
        } catch (\Throwable $exception) {
            Log::error('IMAP: test de connexion échoué', $this->logContext($project, $config) + [
                'folder' => $config['folder'],
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $client->disconnect();
        }
    }

    /**
     * Read the mailbox and import the emails of known prospects.
     *
     * @return array{scanned:int, imported:int, ignored:int, duplicates:int}
     */
    public function sync(Project $project): array
    {
        $config = $this->config($project);

        Log::info('IMAP: début de la synchronisation', $this->logContext($project, $config) + [
            'folder' => $config['folder'],
            'since' => $this->cutoff($config)->toDateTimeString(),
            'max_messages' => self::MAX_PER_SYNC,
        ]);

        try {
            $client = $this->connect($config);
        } catch (\Throwable $exception) {
            Log::error('IMAP: connexion impossible', $this->logContext($project, $config) + [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $counts = ['scanned' => 0, 'imported' => 0, 'ignored' => 0, 'duplicates' => 0];
        $startedAt = microtime(true);

        try {
            $folder = $client->getFolder($config['folder']);

            if (!$folder) {
                throw new \RuntimeException("Le dossier {$config['folder']} est introuvable sur le serveur IMAP.");
            }

            $cutoff = $this->cutoff($config);
            $upToDate = false;

            // 1) Headers only, newest first: the mailbox is read from the day
            //    of the last sync, and the loop stops at the first message
            //    older than the exact date/time of that sync. Nothing older
            //    than the last sync is downloaded.
            $query = $folder->query()
                ->setFetchOrder('desc')
                ->setFetchBody(false)
                ->limit(self::MAX_PER_SYNC)
                ->whereSince($cutoff->copy()->startOfDay());

            $pending = []; // uid => Message-ID

            foreach ($query->get() as $imapMessage) {
                $counts['scanned']++;

                $date = $this->date($imapMessage);

                if ($date->lessThan($cutoff)) {
                    $upToDate = true;
                    break;
                }

                $from = $this->fromAddress($imapMessage);
                $prospect = $from ? $this->prospect($project, $from) : null;

                if (!$from || !$prospect) {
                    $counts['ignored']++;

                    Log::debug('IMAP: email ignoré (expéditeur sans prospect)', [
                        'project_id' => $project->id,
                        'from' => $from,
                        'subject' => Str::limit($this->subject($imapMessage), 120, ''),
                        'date' => $date->toDateTimeString(),
                    ]);

                    continue;
                }

                $uid = $imapMessage->getUid();

                if (!$uid) {
                    $counts['ignored']++;

                    Log::warning('IMAP: email ignoré (uid introuvable)', [
                        'project_id' => $project->id,
                        'from' => $from,
                        'subject' => Str::limit($this->subject($imapMessage), 120, ''),
                        'date' => $date->toDateTimeString(),
                    ]);

                    continue;
                }

                Log::info('IMAP: email correspond à un prospect', [
                    'project_id' => $project->id,
                    'from' => $from,
                    'prospect_id' => $prospect->id,
                    'subject' => Str::limit($this->subject($imapMessage), 120, ''),
                    'date' => $date->toDateTimeString(),
                    'uid' => $uid,
                ]);

                $pending[$uid] = $this->messageId($project, $config, $imapMessage);
            }

            // One query for all the Message-ID already imported
            if ($pending) {
                $known = Message::withTrashed()
                    ->whereIn('message_id', array_values($pending))
                    ->pluck('message_id')
                    ->flip();

                foreach ($pending as $uid => $messageId) {
                    if ($known->has($messageId)) {
                        $counts['duplicates']++;
                        unset($pending[$uid]);

                        Log::info('IMAP: email déjà importé', [
                            'project_id' => $project->id,
                            'uid' => $uid,
                            'message_id' => $messageId,
                        ]);
                    }
                }
            }

            // 2) Bodies are downloaded only for the emails to import. One
            //    query per message: whereUidIn() is rejected by some servers
            //    (OVH answers "BAD parse error: zero-length content").
            foreach (array_keys($pending) as $uid) {
                foreach ($folder->query()->whereUid($uid)->get() as $imapMessage) {
                    $result = $this->store($project, $config, $imapMessage);

                    if ($result === 'imported') {
                        $counts['imported']++;
                    } elseif ($result === 'duplicate') {
                        $counts['duplicates']++;
                    } else {
                        $counts['ignored']++;
                    }
                }
            }

            $this->saveState($project, $counts, $upToDate || $counts['scanned'] < self::MAX_PER_SYNC);

            Log::info('IMAP: synchronisation terminée', $this->logContext($project, $config) + $counts + [
                'up_to_date' => $upToDate || $counts['scanned'] < self::MAX_PER_SYNC,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (\Throwable $exception) {
            $this->saveState($project, $counts, false, $exception->getMessage());

            Log::error('IMAP: synchronisation échouée', $this->logContext($project, $config) + $counts + [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $client->disconnect();
        }

        return $counts;
    }

    /**
     * Import one message; returns "imported", "duplicate" or "ignored".
     */
    protected function store(Project $project, array $config, $imapMessage): string
    {
        $from = $this->fromAddress($imapMessage);
        if (!$from) {
            Log::debug('IMAP: email ignoré (expéditeur illisible)', [
                'project_id' => $project->id,
                'uid' => $imapMessage->getUid(),
            ]);

            return 'ignored';
        }

        $prospect = $this->prospect($project, $from);
        if (!$prospect) {
            Log::debug('IMAP: email ignoré (expéditeur sans prospect)', [
                'project_id' => $project->id,
                'from' => $from,
                'uid' => $imapMessage->getUid(),
            ]);

            return 'ignored';
        }

        $messageId = $this->messageId($project, $config, $imapMessage);

        if (Message::withTrashed()->where('message_id', $messageId)->exists()) {
            Log::debug('IMAP: email déjà importé', [
                'project_id' => $project->id,
                'from' => $from,
                'message_id' => $messageId,
            ]);

            return 'duplicate';
        }

        $subject = $this->subject($imapMessage);
        $text = trim((string) $imapMessage->getTextBody());
        $html = trim((string) $imapMessage->getHTMLBody());

        // Never store an empty email: a message without body nor subject is
        // most of the time an unreadable/partial message.
        if ($html === '' && $text === '' && $subject === '') {
            Log::warning('IMAP: email ignoré (corps et sujet vides)', [
                'project_id' => $project->id,
                'from' => $from,
                'message_id' => $messageId,
            ]);

            return 'ignored';
        }

        $body = $html !== '' ? $html : nl2br(e($text));
        $date = $this->date($imapMessage);

        $message = new Message();
        $message->forceFill([
            'prospect_id' => $prospect->id,
            'thread_id' => $this->thread($project, $config['username'])->id,
            'message_id' => $messageId,
            'subject' => Str::limit($subject, 255, ''),
            'body' => $body,
            'plain_text' => $this->plainText($body),
            'sent' => 1,
            'from_user' => 0,
            'creator_id' => null,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        // No model event: neither the field renderers (the body of a received
        // email must not be rewritten) nor the sending jobs (it is incoming).
        Message::withoutEvents(function () use ($message) {
            $message->save();
        });

        Log::info('IMAP: email importé', [
            'project_id' => $project->id,
            'message_id' => $message->id,
            'thread_id' => $message->thread_id,
            'prospect_id' => $prospect->id,
            'from' => $from,
            'subject' => Str::limit($subject, 120, ''),
            'sent_at' => $date->toDateTimeString(),
        ]);

        return 'imported';
    }

    /**
     * Sender address of the message, lowercased.
     */
    protected function fromAddress($imapMessage): ?string
    {
        $from = $imapMessage->getFrom();

        // webklex returns an Attribute wrapping the address list (the list is
        // obtained with all()); some versions return the list directly.
        if (is_object($from) && method_exists($from, 'all')) {
            $from = $from->all();
        }

        if (is_array($from)) {
            $from = reset($from) ?: null;

            if (is_object($from) && method_exists($from, 'all')) {
                $items = $from->all();
                $from = is_array($items) ? (reset($items) ?: null) : $items;
            }
        }

        $mail = is_object($from) ? ($from->mail ?? null) : (is_array($from) ? ($from['mail'] ?? null) : null);

        if (!$mail) {
            return null;
        }

        $mail = mb_strtolower(trim((string) $mail));

        return $mail !== '' ? $mail : null;
    }

    /**
     * Decoded subject: the raw header is MIME encoded (=?utf-8?q?...?=).
     */
    protected function subject($imapMessage): string
    {
        $subject = trim((string) $imapMessage->getSubject());

        if ($subject === '') {
            return '';
        }

        $decoded = iconv_mime_decode($subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        return trim($decoded !== false ? $decoded : $subject);
    }

    /**
     * Prospect of the project owning this email address.
     */
    protected function prospect(Project $project, string $email)
    {
        return $project->prospects()
            ->whereNotNull('email')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();
    }

    /**
     * Message-ID header, used to import a message only once. A message
     * without one gets an identifier built from its IMAP uid.
     */
    protected function messageId(Project $project, array $config, $imapMessage): string
    {
        $messageId = trim((string) $imapMessage->getMessageId());

        if ($messageId !== '') {
            return mb_substr($messageId, 0, 255);
        }

        $uid = $imapMessage->getUid() ?: $imapMessage->getSequenceId();

        return 'imap.' . $project->id . '.' . md5($config['username'] . '|' . $config['folder'] . '|' . $uid) . '@local';
    }

    protected function date($imapMessage): Carbon
    {
        try {
            $date = Carbon::parse((string) $imapMessage->getDate());
        } catch (\Throwable $exception) {
            return Carbon::now();
        }

        return $date->isFuture() ? Carbon::now() : $date;
    }

    /**
     * Thread receiving the incoming emails: the project thread bound to the
     * mailbox address, else the first public thread, else a new one.
     */
    protected function thread(Project $project, string $mailbox): Thread
    {
        $thread = $project->threads()
            ->whereRaw('LOWER(send_to) = ?', [mb_strtolower($mailbox)])
            ->orderBy('id')
            ->first();

        if ($thread) {
            return $thread;
        }

        $thread = $project->threads()
            ->where('private', false)
            ->orderBy('order')
            ->orderBy('id')
            ->first();

        if ($thread) {
            return $thread;
        }

        $thread = $project->threads()->orderBy('id')->first();

        if ($thread) {
            return $thread;
        }

        $thread = $project->threads()->create([
            'name' => 'Email',
            'slug' => 'email-' . Str::lower(Str::random(6)),
            'private' => false,
            'send_to' => $mailbox,
            'order' => 1,
        ]);

        Log::info('IMAP: création du thread "Email"', [
            'project_id' => $project->id,
            'thread_id' => $thread->id,
            'send_to' => $mailbox,
        ]);

        return $thread;
    }

    /**
     * Plain text of the email, for the prospects search.
     */
    protected function plainText(string $body): string
    {
        $text = preg_replace('/<br\s*\/?>|<\/p>|<\/div>/i', "\n", $body);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\R+/u', "\n", $text);

        return mb_strtolower(trim((string) $text));
    }

    /**
     * Exact start of the window (date and time): the last synchronisation,
     * or the last days on the first run. IMAP only filters by day (SINCE),
     * so this precise value is used to stop the reading.
     */
    protected function cutoff(array $config): Carbon
    {
        if (empty($config['last_sync_at'])) {
            return Carbon::now()->subDays(self::DEFAULT_WINDOW_DAYS);
        }

        try {
            return Carbon::parse($config['last_sync_at']);
        } catch (\Throwable $exception) {
            return Carbon::now()->subDays(self::DEFAULT_WINDOW_DAYS);
        }
    }

    /**
     * Context added to every log line, without the password.
     */
    protected function logContext(Project $project, array $config): array
    {
        return [
            'project_id' => $project->id,
            'project' => $project->name ?? null,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'username' => $config['username'] ?? null,
        ];
    }

    /**
     * IMAP configuration of the project, from the "email" setting.
     */
    protected function config(Project $project): array
    {
        $settings = ProjectSetting::get($project, self::SETTING);
        $imap = data_get($settings, 'imap');

        if (!$imap || !data_get($imap, 'enabled')) {
            throw new \RuntimeException("La récupération des emails entrants (IMAP) n'est pas activée pour ce projet.");
        }

        $provider = (string) data_get($imap, 'provider');
        $preset = self::PROVIDERS[$provider] ?? [];

        $host = trim((string) (data_get($imap, 'host') ?: data_get($preset, 'host')));
        $port = (int) (data_get($imap, 'port') ?: data_get($preset, 'port') ?: 993);
        $encryption = data_get($imap, 'encryption') ?: data_get($preset, 'encryption') ?: 'ssl';
        $username = trim((string) data_get($imap, 'username'));
        $password = $this->decrypt((string) data_get($imap, 'password'));

        if ($host === '' || $username === '' || $password === '') {
            throw new \RuntimeException("Renseignez le serveur, l'identifiant et le mot de passe IMAP.");
        }

        return [
            'host' => $host,
            'port' => $port,
            'encryption' => in_array($encryption, ['none', 'false', '0'], true) ? false : $encryption,
            'username' => $username,
            'password' => $password,
            'folder' => trim((string) data_get($imap, 'folder')) ?: self::DEFAULT_FOLDER,
            'last_sync_at' => data_get($imap, 'last_sync_at'),
        ];
    }

    /**
     * The password is stored encrypted by the settings form; a value stored in
     * clear text by an older version is still accepted.
     */
    protected function decrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $exception) {
            return $value;
        }
    }

    protected function connect(array $config)
    {
        $client = (new ClientManager())->make([
            'host' => $config['host'],
            'port' => $config['port'],
            'encryption' => $config['encryption'],
            'validate_cert' => true,
            'username' => $config['username'],
            'password' => $config['password'],
            'protocol' => 'imap',
            'timeout' => 30,
        ]);

        $client->connect();

        return $client;
    }

    /**
     * Remember the sync result on the "email" setting.
     */
    protected function saveState(Project $project, array $counts, bool $upToDate, ?string $error = null): void
    {
        $settings = ProjectSetting::get($project, self::SETTING);
        $settings = $settings ? json_decode(json_encode($settings), true) : [];

        $settings['imap'] = array_merge($settings['imap'] ?? [], [
            'last_sync_at' => $upToDate ? now()->toIso8601String() : data_get($settings, 'imap.last_sync_at'),
            'last_synced' => $counts['imported'],
            'last_error' => $error,
        ]);

        ProjectSetting::set($project, self::SETTING, $settings);
    }
}
