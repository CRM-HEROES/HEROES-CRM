<?php

namespace App\Jobs\SMS;

use App\Models\Line;
use App\Models\Sms;
use App\Services\CloudTalk as CloudTalkService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CloudTalk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Sms $sms;

    /**
     * Create a new job instance.
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('cloudtalk-sms-'.$this->sms->id))->dontRelease()->expireAfter(120)];
    }

    /**
     * Execute the job.
     */
    public function handle(CloudTalkService $cloudTalk): void
    {
        try {
            $this->sms->refresh();
            if ($this->sms->sent_at || $this->sms->cloudtalk_message_id) {
                return;
            }
            $line = $this->cloudTalkLine();

            if (!$line) {
                throw new Exception('Aucune ligne CloudTalk SMS affectee a cet agent.');
            }

            $project = $this->sms->prospect->project;
            $shouldSend = DB::transaction(function () use ($project, $line) {
                $project->newQuery()->whereKey($project->id)->lockForUpdate()->first();
                $this->sms->refresh();
                if ($this->sms->sent_at || $this->sms->cloudtalk_message_id) {
                    return false;
                }
                $this->sms->updateQuietly([
                    'cloudtalk_sender_number' => preg_replace('/\D+/', '', (string) $line->numero),
                    'cloudtalk_send_started_at' => Carbon::now(),
                    'error' => null,
                ]);

                return true;
            }, 3);
            if (!$shouldSend) {
                return;
            }

            $response = $cloudTalk->sendSms(
                $line,
                (string) $this->sms->prospect->mobile_phone_number,
                (string) $this->sms->message,
                $this->sms->media ?? []
            );

            $messageId = $cloudTalk->smsMessageId($response);
            DB::transaction(function () use ($project, $messageId) {
                $project->newQuery()->whereKey($project->id)->lockForUpdate()->first();
                $this->sms->refresh();
                $attributes = ['sent_at' => $this->sms->sent_at ?: Carbon::now(), 'error' => null];
                $messageId = $this->sms->cloudtalk_message_id ?: $messageId;
                if ($messageId !== null) {
                    $attributes['cloudtalk_message_id'] = $messageId;
                    // An early callback can have imported the provider record before its API response arrived.
                    $duplicates = Sms::query()->where('prospect_id', $this->sms->prospect_id)
                        ->where('source', 'cloudtalk')->where('is_incoming', false)->where('from_user', true)
                        ->where('cloudtalk_message_id', $messageId)->whereNull('cloudtalk_send_started_at')
                        ->whereKeyNot($this->sms->id)->get();
                    foreach ($duplicates as $duplicate) {
                        $attributes['cloudtalk_event_id'] ??= $this->sms->cloudtalk_event_id ?: $duplicate->cloudtalk_event_id;
                        if (!$this->sms->media && $duplicate->media) {
                            $attributes['media'] = $duplicate->media;
                        }
                        // Keep the original CRM record and its attachment files intact.
                        $duplicate->deleteQuietly();
                    }
                }
                $this->sms->updateQuietly($attributes);
            }, 3);
        } catch (Exception $e) {
            $this->sms->refresh();
            if ($this->sms->sent_at || $this->sms->cloudtalk_message_id) {
                return;
            }
            $this->sms->update(['error' => $e->getMessage()]);
            \ProjectLog::error($this->sms->prospect->project, "CloudTalk: " . $e->getMessage());
        }
    }

    protected function cloudTalkLine(): ?Line
    {
        return $this->sms
            ->prospect
            ->project
            ->lines()
            ->where('operator', 'cloudtalk')
            ->where('user_id', $this->sms->creator_id)
            ->get()
            ->first(fn (Line $line) => !empty($line->numero));
    }
}
