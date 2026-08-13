<?php

namespace App\Jobs;

use App\Models\Interaction;
use App\Models\KavkomCall;
use App\Models\UserSetting;
use App\Services\Anthropic;
use App\Services\ProspectCallDataMerger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessKavkomCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [60, 180, 600, 1800];

    public function __construct(public int $callId) {}

    public function handle(Anthropic $anthropic, ProspectCallDataMerger $merger): void
    {
        $call = KavkomCall::with('prospect')->findOrFail($this->callId);
        if ($call->processed_at || !$call->prospect) {
            Log::channel('kavkom')->info('Kavkom call skipped.', ['call_uuid' => $call->call_uuid, 'processed' => (bool) $call->processed_at, 'has_prospect' => (bool) $call->prospect]);
            return;
        }

        try {
            if (!$call->recording_url) throw new \RuntimeException('Recording URL missing from Kavkom CDR.');
            Log::channel('kavkom')->info('Kavkom transcription started.', ['call_uuid' => $call->call_uuid, 'prospect_id' => $call->prospect_id]);
            $audio = $this->downloadRecording($call, $call->recording_url);
            try {
                $transcript = $this->transcribe($audio['absolute_path']);
            } finally {
                Storage::disk('local')->delete($audio['path']);
                Log::channel('kavkom')->info('Temporary Kavkom recording deleted.', ['call_uuid' => $call->call_uuid]);
            }
            $analysis = $this->analyse($anthropic, $transcript);
            $interaction = $this->storeInteraction($call, $transcript, $analysis, $merger);

            $call->update(['interaction_id' => $interaction->id, 'status' => 'processed', 'processed_at' => now(), 'error' => null]);
            Log::channel('kavkom')->info('Kavkom call processed.', ['call_uuid' => $call->call_uuid, 'interaction_id' => $interaction->id]);
        } catch (\Throwable $e) {
            $call->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 600)]);
            Log::channel('kavkom')->warning('Kavkom call processing failed.', ['call_uuid' => $call->call_uuid, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function downloadRecording(KavkomCall $call, string $url): array
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https'
            || ($host !== 'kavkom.com' && !str_ends_with($host, '.kavkom.com'))) {
            throw new \RuntimeException('The recording URL must be an HTTPS URL hosted by Kavkom.');
        }

        $request = Http::timeout(120)->accept('*/*');
        if ($call->user_id && ($setting = UserSetting::where('user_id', $call->user_id)->whereNull('project_id')->where('key', 'kavkom')->first())) {
            $token = data_get($setting->value, 'api_token');
            if ($token) $request = $request->withHeaders(['X-API-TOKEN' => $token]);
        }
        $response = $request->get($url);
        if (!$response->successful() || $response->body() === '') {
            throw new \RuntimeException('Unable to download the Kavkom recording (HTTP '.$response->status().').');
        }

        $contentType = strtolower(trim(strtok((string) $response->header('Content-Type'), ';')));
        $size = strlen($response->body());
        $extension = $this->extension($contentType, $url);
        $supportedExtensions = ['flac', 'm4a', 'mp3', 'mp4', 'mpeg', 'mpga', 'ogg', 'wav', 'webm'];
        $isAudio = str_starts_with($contentType, 'audio/')
            || ($contentType === 'application/octet-stream' && in_array($extension, $supportedExtensions, true));
        if (!$isAudio || $size > 25 * 1024 * 1024) {
            throw new \RuntimeException('Kavkom recording is not a supported audio file or exceeds Whisper’s 25 MB limit.');
        }

        $path = 'kavkom-transcriptions/'.$call->call_uuid.'.'.$extension;
        Storage::disk('local')->put($path, $response->body());

        Log::channel('kavkom')->info('Kavkom recording downloaded temporarily.', ['call_uuid' => $call->call_uuid, 'size_bytes' => $size, 'content_type' => $contentType]);
        return ['path' => $path, 'absolute_path' => Storage::disk('local')->path($path)];
    }

    private function transcribe(string $audioPath): string
    {
        $key = config('services.openai.key');
        if (!$key) throw new \RuntimeException('OPENAI_API_KEY is not configured.');

        $response = Http::withToken($key)->timeout(300)->attach('file', fopen($audioPath, 'r'), basename($audioPath))->post('https://api.openai.com/v1/audio/transcriptions', [
            'model' => config('services.openai.transcription_model'),
            'language' => 'fr',
            'response_format' => 'verbose_json',
        ]);
        if (!$response->successful() || !data_get($response->json(), 'text')) {
            throw new \RuntimeException('OpenAI transcription failed (HTTP '.$response->status().').');
        }
        Log::channel('kavkom')->info('Whisper transcription completed.', ['characters' => mb_strlen((string) data_get($response->json(), 'text'))]);
        return data_get($response->json(), 'text');
    }

    private function analyse(Anthropic $anthropic, string $transcript): array
    {
        $prompt = <<<'PROMPT'
Analyse cet appel commercial. Ne déduis jamais une information absente. Retourne uniquement un objet JSON valide avec les clés : summary (string), qualification (hot|warm|cold|unqualified|unknown), needs (array), objections (array), products_services (array), next_steps (array), extracted (objet avec first_name,last_name,email,phone_number,mobile_phone_number,company_name,job_title,website_url,street,postal_code,city,country,budget,project; valeurs string ou null).

TRANSCRIPTION :
PROMPT;
        $raw = $anthropic->sendMessage([['role' => 'user', 'content' => $prompt."\n".$transcript]], 1800);
        $json = json_decode(trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/', '', $raw)), true);
        if (!is_array($json)) throw new \RuntimeException('Claude returned an invalid call-analysis response.');
        return $json;
    }

    private function storeInteraction(KavkomCall $call, string $transcript, array $analysis, ProspectCallDataMerger $merger): Interaction
    {
        $interaction = $call->interaction ?: $call->prospect->interactions()->make();
        $interaction->fill(['creator_id' => $call->user_id, 'from_user' => true, 'number' => $call->destination, 'source' => 'kavkom', 'status' => 'completed', 'ended_at' => $call->completed_at ?: now(), 'path' => null, 'size' => 0, 'data' => ['call_uuid' => $call->call_uuid, 'transcript' => $transcript, 'analysis' => $analysis, 'recording_deleted_at' => now()->toIso8601String()]])->save();

        $updates = $merger->buildProspectUpdates($call->prospect, $analysis);
        $meta = $merger->buildMeta($call->prospect, $analysis, 'kavkom_last_analysis');
        $meta['kavkom_last_analysis']['call_uuid'] = $call->call_uuid;
        $updates['meta'] = $meta;
        $call->prospect->update($updates);
        return $interaction;
    }

    private function extension(?string $contentType, string $url): string
    {
        return match (strtolower((string) $contentType)) { 'audio/mpeg', 'audio/mp3' => 'mp3', 'audio/wav', 'audio/x-wav' => 'wav', 'audio/ogg' => 'ogg', 'audio/mp4', 'audio/m4a' => 'm4a', default => pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp3' };
    }
}
