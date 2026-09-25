<?php

namespace App\Jobs\AiCalls;

use App\Models\AiAgent;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CallProspectWithAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $prospectId,
        public ?int $agentId = null,
        public int $maxConcurrent = 1
    ) {
        $this->onQueue('call_ai');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $prospect = Prospect::query()->find($this->prospectId);

        if (!$prospect) {
            Log::warning('AI call job skipped: prospect not found.', [
                'prospect_id' => $this->prospectId,
            ]);
            return;
        }

        $destination = trim((string) ($prospect->mobile_phone_number ?: $prospect->phone_number));
        if ($destination === '') {
            Log::warning('AI call job skipped: no phone number.', [
                'prospect_id' => $prospect->id,
            ]);
            return;
        }

        $agent = $this->resolveAgent($prospect);
        if (!$agent) {
            Log::warning('AI call job skipped: no active AI agent available.', [
                'prospect_id' => $prospect->id,
                'project_id' => $prospect->project_id,
                'requested_agent_id' => $this->agentId,
            ]);
            return;
        }

        $this->waitForAvailableChannel();

        $openingPrompt = trim((string) (($agent->script ?? '') . "\n" . ($agent->instructions ?? '')));
        $projectSlug = $prospect->project ? $prospect->project->slug : null;
        $payload = [
            'phoneNumber' => $destination,
            'prospectId' => $prospect->id,
            'projectId' => $prospect->project_id,
            'projectSlug' => $projectSlug,
            'agentId' => $agent->id,
            'callerNumber' => $prospect->mobile_phone_number ?: $prospect->phone_number,
            'destinationNumber' => $destination,
            'openingPrompt' => $openingPrompt !== '' ? $openingPrompt : null,
            'prompt' => $openingPrompt !== '' ? $openingPrompt : null,
        ];

        $response = Http::timeout(20)->post(config('services.ia_gateway.base_url') . '/call', $payload);

        if ($response->successful()) {
            Log::info('AI call launched successfully.', [
                'prospect_id' => $prospect->id,
                'agent_id' => $agent->id,
                'destination' => $destination,
                'response' => $response->json(),
                'opening_prompt' => $openingPrompt,
            ]);

            $meta = is_array($prospect->meta) ? $prospect->meta : [];
            $meta['ai_call_last_started_at'] = now()->toDateTimeString();
            $meta['ai_call_last_agent_id'] = $agent->id;
            $prospect->meta = $meta;
            $prospect->save();

            return;
        }

        Log::error('AI call launch failed.', [
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'destination' => $destination,
            'status' => $response->status(),
            'body' => $response->body(),
            'opening_prompt' => $openingPrompt,
        ]);
    }

    protected function resolveAgent(Prospect $prospect): ?AiAgent
    {
        if ($this->agentId) {
            return $prospect->project
                ->aiAgents()
                ->whereKey($this->agentId)
                ->where('is_active', true)
                ->first();
        }

        if ($prospect->ai_agent_id) {
            return $prospect->project
                ->aiAgents()
                ->whereKey($prospect->ai_agent_id)
                ->where('is_active', true)
                ->first();
        }

        return $prospect->project
            ->aiAgents()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    protected function waitForAvailableChannel(): void
    {
        $deadline = now()->addMinutes(5);

        while (now() < $deadline) {
            $response = Http::timeout(5)->get(config('services.ia_gateway.base_url') . '/channels');
            $activeCalls = 0;

            if ($response->successful()) {
                $payload = $response->json();
                $activeCalls = (int) (
                    $payload['active']
                    ?? $payload['count']
                    ?? $payload['channels']
                    ?? $payload['active_calls']
                    ?? 0
                );
            }

            if ($activeCalls < $this->maxConcurrent) {
                return;
            }

            sleep(5);
        }

        throw new \RuntimeException('AI call channel limit reached before the job could launch.');
    }
}
