<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class QuestionnaireRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('prospect_questionnaire')
            ->select(DB::raw('questionnaire_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('questionnaire_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "questionnaires";
    }

    /**
     *
     */
    public function getName()
    {
        return "Questionnaire relevance";
    }

}
