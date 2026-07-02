<?php

namespace App\Console\Commands\Relevance;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UserMessageRelevance extends UserProjectRelevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('user_message')
            ->select(DB::raw('user_message.user_id as user_id'), DB::raw('threads.project_id as project_id'), DB::raw('count(*) as relevance'))
            ->join('messages', 'messages.id', '=', 'user_message.message_id')
            ->join('threads', 'threads.id', '=', 'messages.thread_id')
            ->groupBy('user_message.user_id', 'threads.project_id')
            ->where('user_message.created_at', '>=', $from)
            ->whereNotNull('user_message.user_id')
            ->whereNotNull('threads.project_id')
            ->get();
    }

    /**
     *
     */
    public function getField()
    {
        return "relevance_message";
    }

    /**
     *
     */
    public function getName()
    {
        return "User message relevance";
    }

}
