<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class FolderRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('files')
            ->select(DB::raw('folder_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('folder_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "folders";
    }

    /**
     *
     */
    public function getName()
    {
        return "Folder relevance";
    }

}
