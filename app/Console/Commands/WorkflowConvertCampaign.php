<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Workflows\Legacy\LegacyCampaignConverter;
use App\Workflows\Legacy\LegacyConversionException;
use App\Workflows\WorkflowValidationException;
use Illuminate\Console\Command;

class WorkflowConvertCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:convert-campaign {campaign : Id of the campaign} {--activate : Hand the campaign over to the workflow (activate it, switch the campaign off, process the prospects already concerned)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert a legacy campaign to a workflow (a draft, unless --activate)';

    /**
     * Without --activate the campaign is not touched at all: the workflow is
     * created as a draft, to be reviewed in the builder. With --activate the
     * campaign is handed over (see LegacyCampaignConverter::cutover()).
     */
    public function handle(LegacyCampaignConverter $converter)
    {
        $campaign = Campaign::query()->find($this->argument('campaign'));

        if (!$campaign) {
            $this->error('Campaign not found.');
            return self::FAILURE;
        }

        $workflow = $converter->workflowOf($campaign);

        if (!$workflow) {
            $analysis = $converter->analyze($campaign);

            try {
                $workflow = $converter->convert($campaign);
            } catch (LegacyConversionException $e) {
                $this->error("The campaign #{$campaign->id} cannot be converted:");
                foreach ($e->reasons as $reason) {
                    $this->line(" - $reason");
                }
                return self::FAILURE;
            }

            $this->info("Workflow #{$workflow->id} created from the campaign #{$campaign->id} (draft).");

            foreach ($analysis['notes'] as $note) {
                $this->line(" - $note");
            }
        } else {
            $this->line("The campaign #{$campaign->id} was already converted (workflow #{$workflow->id}).");
        }

        if (!$this->option('activate')) {
            $this->line('The campaign keeps running. Review the workflow, then run again with --activate to hand over.');
            return self::SUCCESS;
        }

        try {
            $converter->cutover($campaign, $workflow);
        } catch (WorkflowValidationException $e) {
            $this->error('The workflow cannot be activated; the campaign is left running:');
            foreach ($e->errors as $error) {
                $this->line(" - $error");
            }
            return self::FAILURE;
        }

        $this->info("Workflow #{$workflow->id} activated, campaign #{$campaign->id} switched off, prospects already concerned queued.");

        return self::SUCCESS;
    }
}
