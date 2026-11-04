<?php

namespace App\Agents;

use App\Agents\Claude\ClaudeResponse;
use App\Models\AgentRun;
use App\Models\AgentUsageDaily;

/**
 * Tokens and cost of the runs, per run and per user, project and day.
 */
class UsageMeter
{
    /**
     * Cost in micro-dollars (a price per million tokens times a number of
     * tokens is a number of millionths of dollar).
     */
    public function cost(string $model, ClaudeResponse $response): int
    {
        $price = config("agents.pricing.$model");

        if (!$price) {
            return 0;
        }

        return (int) round(
            $response->inputTokens * $price['input']
            + $response->outputTokens * $price['output']
            + $response->cacheReadTokens * ($price['cache_read'] ?? $price['input'] * 0.1)
            + $response->cacheWriteTokens * ($price['cache_write'] ?? $price['input'] * 1.25)
        );
    }

    /**
     * Adds one answer of the model to the run and to the daily total.
     */
    public function record(AgentRun $run, ClaudeResponse $response): void
    {
        $cost = $this->cost($run->model, $response);

        $run->increment('input_tokens', $response->inputTokens);
        $run->increment('output_tokens', $response->outputTokens);
        $run->increment('cache_read_tokens', $response->cacheReadTokens);
        $run->increment('cache_write_tokens', $response->cacheWriteTokens);
        $run->increment('cost_micro', $cost);

        $day = AgentUsageDaily::firstOrCreate([
            'user_id' => $run->user_id,
            'project_id' => $run->project_id,
            'date' => now()->toDateString(),
        ]);

        $day->increment('input_tokens', $response->inputTokens);
        $day->increment('output_tokens', $response->outputTokens);
        $day->increment('cache_read_tokens', $response->cacheReadTokens);
        $day->increment('cost_micro', $cost);
    }

    /**
     * Counts the run once in the daily total.
     */
    public function countRun(AgentRun $run): void
    {
        AgentUsageDaily::firstOrCreate([
            'user_id' => $run->user_id,
            'project_id' => $run->project_id,
            'date' => now()->toDateString(),
        ])->increment('runs');
    }
}
