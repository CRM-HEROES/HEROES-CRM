<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * Send message to a thread associated to
 * the prospect
 * 
 * Data format:
 * {
 *    thread: {thread ID, thread in which we send the message},
 *    body: {body, body of the message},
 * }
 */
class ProspectMessageAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        if (!$this->action->value) {
            return;
        }
        
        if (!isset($this->action->value['body']) || !$this->action->value['body']) {
            return;
        }

        if (!isset($this->action->value['thread']) || !$this->action->value['thread']) {
            return;
        }

        $thread = DB::table('threads')
            ->where('id', $this->action->value['thread'])
            ->where('project_id', $this->model->project_id)
            ->first(['id']);
    
        if (!$thread) {
            return;
        }

        $message = $this->model
            ->messages()
            ->create([
                'body' => $this->action->value['body'],
                'thread_id' => $thread->id,
                'from_user' => 1
            ]);

        // dd($message);

        if (!isset($this->action->value['user']) || !$this->action->value['user']) {
            return;
        }

        $user = DB::table('users')
            ->join('user_project', 'user_project.user_id', '=', 'users.id')
            ->where('users.id', $this->action->value['user'])
            ->where('user_project.project_id', $this->model->project_id)
            ->first(['id']);
        
        if (!$user) {
            return;
        }

        $message->users()->syncWithoutDetaching($user->id);
    }
}