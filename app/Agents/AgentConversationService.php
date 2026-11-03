<?php

namespace App\Agents;

use App\Jobs\Agent\RunAgentTurn;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\AgentRun;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Receives a message for an agent: stores it with the context of the moment
 * and starts the run that answers it.
 */
class AgentConversationService
{
    public function __construct(protected PromptBuilder $prompts)
    {
    }

    /**
     * @throws AgentRequestException when the message cannot be accepted
     * @throws AgentAccessException when the user is not in the project
     */
    public function ask(Agent $agent, User $user, Project $project, string $text, ?int $conversationId = null): AgentRun
    {
        $text = trim($text);

        if ($text === '') {
            throw new AgentRequestException('Le message est vide.');
        }

        if (mb_strlen($text) > (int) config('agents.max_message_chars')) {
            throw new AgentRequestException('Le message est trop long.');
        }

        if ((int) $agent->user_id !== (int) $user->id) {
            throw new AgentRequestException("Cet agent ne vous appartient pas.");
        }

        if (!$agent->usableIn($project->id)) {
            throw new AgentRequestException("Cet agent est désactivé ou n'est pas disponible dans ce projet.");
        }

        if (!(new AgentExecutionContext($user, $project))->ownerHasAccess()) {
            throw new AgentAccessException("Vous n'avez pas accès à ce projet.");
        }

        $run = DB::transaction(function () use ($agent, $user, $project, $text, $conversationId) {
            if ($conversationId) {
                $conversation = AgentConversation::query()
                    ->whereKey($conversationId)
                    ->where('agent_id', $agent->id)
                    ->where('user_id', $user->id)
                    ->where('project_id', $project->id)
                    ->lockForUpdate()
                    ->first();

                if (!$conversation) {
                    throw new AgentRequestException('Conversation introuvable.');
                }
            } else {
                $conversation = AgentConversation::create([
                    'agent_id' => $agent->id,
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'title' => mb_substr($text, 0, 80),
                    'status' => AgentConversation::OPEN,
                ]);
            }

            // One answer at a time per conversation: the history is a sequence
            if ($conversation->runs()->whereIn('status', [AgentRun::PENDING, AgentRun::RUNNING])->exists()) {
                throw new AgentRequestException("L'agent est encore en train de répondre.");
            }

            AgentMessage::create([
                'conversation_id' => $conversation->id,
                'role' => AgentMessage::USER,
                'content' => $this->prompts->turn($text, $user, $project),
            ]);

            $conversation->forceFill(['last_message_at' => now()])->save();

            return AgentRun::create([
                'conversation_id' => $conversation->id,
                'agent_id' => $agent->id,
                'agent_version_id' => $agent->active_version_id,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'trigger' => AgentRun::USER,
                'status' => AgentRun::PENDING,
                'model' => $agent->model,
            ]);
        });

        RunAgentTurn::dispatch($run->id);

        return $run;
    }
}
