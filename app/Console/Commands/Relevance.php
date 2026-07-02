<?php

namespace App\Console\Commands;

use App\Console\Commands\Relevance\CalendarRelevance;
use App\Console\Commands\Relevance\CategoryRelevance;
use App\Console\Commands\Relevance\FolderRelevance;
use App\Console\Commands\Relevance\GroupRelevance;
use App\Console\Commands\Relevance\LabelRelevance;
use App\Console\Commands\Relevance\OrderActionRelevance;
use App\Console\Commands\Relevance\OrderStatusRelevance;
use App\Console\Commands\Relevance\OrderStepRelevance;
use App\Console\Commands\Relevance\ProductRelevance;
use App\Console\Commands\Relevance\QuestionnaireRelevance;
use App\Console\Commands\Relevance\ThreadRelevance;
use App\Console\Commands\Relevance\UserEventRelevance;
use App\Console\Commands\Relevance\UserMessageRelevance;
use App\Console\Commands\Relevance\UserOrderActionRelevance;
use App\Console\Commands\Relevance\UserProspectRelevance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class Relevance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:relevance {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Item relevance';

    /**
     * 
     */
    protected $relevances;
    protected $userRelevances;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->relevances = [
            new CalendarRelevance(),
            new CategoryRelevance(),
            new FolderRelevance(),
            new GroupRelevance(),
            new LabelRelevance(),
            new OrderActionRelevance(),
            new OrderStatusRelevance(),
            new OrderStepRelevance(),
            new ProductRelevance(),
            new QuestionnaireRelevance(),
            new ThreadRelevance(),
            ];
            
        $this->userRelevances = [
            new UserEventRelevance(),
            new UserMessageRelevance(),
            new UserOrderActionRelevance(),
            new UserProspectRelevance(),
            ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = \Carbon\Carbon::now();
        $date = $date->subDays($this->option('days') ? $this->option('days') : 120);
        $date = $date->format("Y-m-d h:i:s");

        $progressBar = $this->configureRelevanceProgressBar();
        $progressBar->start();

        foreach ($this->relevances as $relevance) {
            $progressBar->setMessage($relevance->getName() . " ...");

            $this->updateRelevance($relevance->getTable(), $relevance->getTableId(), $relevance->getField(), $relevance->getData($date));
            
            $progressBar->advance();
        }

        $progressBar->setMessage('Finished!');
        $progressBar->finish();

        ///////////////////////////////////////////////////
        
        $progressBar = $this->configureUserRelevanceProgressBar();
        $progressBar->start();

        foreach ($this->userRelevances as $relevance) {
            $progressBar->setMessage($relevance->getName() . " ...");

            $this->updateUserRelevance($relevance->getField(), $relevance->getData($date));
            
            $progressBar->advance();
        }

        $progressBar->setMessage('Finished!');
        $progressBar->finish();
        
        return self::SUCCESS;
    }

    /**
     * 
     */
    protected function updateRelevance($table, $tableId, $field, $data)
    {
        DB::update("UPDATE `$table` SET `$field` = 0");

        if (count($data) == 0) {
            return;
        }

        $query = "UPDATE `$table` SET `$field` = CASE ";
        $query .= $data
            ->map(function($count) use($tableId) {
                return "WHEN `$tableId` = {$count->item_id} THEN {$count->relevance} ";
            })
            ->join("");
        $query .= "END ";
        $query .= "WHERE `$tableId` IN (";
        $query .= $data
            ->map(function($count) {
                return $count->item_id;
            })
            ->join(",");
        $query .= ")";

        DB::update($query);
    }

    /**
     * 
     */
    protected function updateUserRelevance($field, $data)
    {
        DB::update("UPDATE `user_project` SET `$field` = 0");

        if (count($data) == 0) {
            return;
        }

        $query = "UPDATE `user_project` SET `$field` = CASE ";
        $query .= $data
            ->map(function($count) {
                return "WHEN (`user_id` = {$count->user_id} AND `project_id` = {$count->project_id}) THEN {$count->relevance} ";
            })
            ->join("");
        $query .= "ELSE `$field` END ";
        /*$query .= "WHERE `user_id` IN (";
        $query .= $data
            ->map(function($count) {
                return $count->user_id;
            })
            ->join(",");
        $query .= ") ";
        $query .= "AND `project_id` IN (";
        $query .= $data
            ->map(function($count) {
                return $count->project_id;
            })
            ->join(",");
        $query .= ")";*/

        DB::update($query);
    }

    /**
     * 
     */
    protected function configureRelevanceProgressBar()
    {
        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $progressBar = $this->output->createProgressBar(count($this->relevances));
        $progressBar->setBarCharacter('|');
        $progressBar->setProgressCharacter('|');
        $progressBar->setEmptyBarCharacter('.');
        $progressBar->setFormat('custom');
        $progressBar->setMessage("Starting ...");

        return $progressBar;
    }

    /**
     * 
     */
    protected function configureUserRelevanceProgressBar()
    {
        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $progressBar = $this->output->createProgressBar(count($this->userRelevances));
        $progressBar->setBarCharacter('|');
        $progressBar->setProgressCharacter('|');
        $progressBar->setEmptyBarCharacter('.');
        $progressBar->setFormat('custom');
        $progressBar->setMessage("Starting ...");

        return $progressBar;
    }
}
