<?php

namespace App\Console\Commands\Relevance;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UserProspectRelevance extends UserProjectRelevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('prospect_user')
            ->select(DB::raw('prospect_user.user_id as user_id'), DB::raw('prospects.project_id as project_id'), DB::raw('count(*) as relevance'))
            ->join('prospects', 'prospects.id', '=', 'prospect_user.prospect_id')
            ->groupBy('prospect_user.user_id', 'prospects.project_id')
            ->where('prospect_user.created_at', '>=', $from)
            ->whereNotNull('prospect_user.user_id')
            ->whereNotNull('prospects.project_id')
            ->get();
    }

    /**
     *
     */
    public function getField()
    {
        return "relevance_prospect";
    }

    /**
     *
     */
    public function getName()
    {
        return "User prospect relevance";
    }

}
