<?php

namespace App\Providers;

use App\Agents\Tools\AppHelpTool;
use App\Agents\Tools\AgentDocsTool;
use App\Agents\Tools\CallStartTool;
use App\Agents\Tools\CallTransferTool;
use App\Agents\Tools\DoNotCallTool;
use App\Agents\Tools\ComparePeriodsTool;
use App\Agents\Tools\CrmAggregateTool;
use App\Agents\Tools\CrmDescribeTool;
use App\Agents\Tools\CrmSearchTool;
use App\Agents\Tools\TeamPerformanceTool;
use App\Agents\Tools\ProjectConfigTool;
use App\Agents\Tools\ProspectHistoryTool;
use App\Agents\Tools\ReportCreateTool;
use App\Agents\Tools\ToolRegistry;
use App\Agents\Tools\WorkflowCatalogTool;
use App\Agents\Tools\WorkflowDraftTool;
use App\Agents\Tools\CampaignSetActiveTool;
use App\Agents\Tools\CampaignStatusTool;
use App\Agents\Tools\CommissionsTool;
use App\Agents\Tools\DocumentGenerateTool;
use App\Agents\Tools\DocumentTemplateInstallTool;
use App\Agents\Tools\DocumentTemplatesTool;
use App\Agents\Tools\ImportConfigureTool;
use App\Agents\Tools\ImportMappingTool;
use App\Agents\Tools\ImportRunTool;
use App\Agents\Tools\IncomingCommunicationsTool;
use App\Agents\Tools\IncomingMarkTreatedTool;
use App\Agents\Tools\ImportStatusTool;
use App\Agents\Tools\MessageTemplatesTool;
use App\Agents\Tools\PipelineOverviewTool;
use App\Agents\Tools\PappersSearchTool;
use App\Agents\Tools\ProjectEntityDeleteTool;
use App\Agents\Tools\ProjectEntitySaveTool;
use App\Agents\Tools\ProjectUserMembershipTool;
use App\Agents\Tools\QuestionnaireBuildTool;
use App\Agents\Tools\QuestionnaireOverviewTool;
use App\Agents\Tools\QuestionnaireResponsesTool;
use App\Agents\Tools\TeamMembersTool;
use App\Agents\Tools\TrashListTool;
use App\Agents\Tools\UserRolesSetTool;
use App\Agents\Tools\TrashRestoreTool;
use App\Agents\Tools\VehiclesTool;
use App\Agents\Tools\WorkflowCancelExecutionTool;
use App\Agents\Tools\WorkflowExecutionsTool;
use App\Agents\Writes\WriteCapabilities;
use App\Http\Requests\Validator\TwoFactorsTokenValidator;
use App\Mail\BrevoTransport;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\Actions\AgentCallAction;
use App\Workflows\Actions\AgentSmsAction;
use App\Workflows\Actions\MakeCallAction;
use App\Workflows\Actions\SendEmailAction;
use App\Workflows\Actions\SendSmsAction;
use App\Calls\Voice\ConfiguredVoiceProvider;
use App\Calls\Voice\VoiceProvider;
use App\Models\Prospect;
use Illuminate\Database\Schema\Builder;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One registry of workflow actions per process. The legacy campaign
        // actions are reused as they are; the communication actions are new.
        $this->app->singleton(ActionRegistry::class, function ($app) {
            $registry = new ActionRegistry();
            $registry->register('send_email', $app->make(SendEmailAction::class));
            $registry->register('send_sms', $app->make(SendSmsAction::class));
            $registry->register('make_call', $app->make(MakeCallAction::class));
            $registry->register('agent_call', $app->make(AgentCallAction::class));
            $registry->register('agent_sms', $app->make(AgentSmsAction::class));

            return $registry;
        });

        // The tools of the AI agents. A new tool is registered here.
        $this->app->singleton(ToolRegistry::class, function ($app) {
            $registry = new ToolRegistry();
            foreach ([CrmDescribeTool::class, CrmSearchTool::class, CrmAggregateTool::class, TeamPerformanceTool::class, ComparePeriodsTool::class, AppHelpTool::class, AgentDocsTool::class, CallStartTool::class, CallTransferTool::class, DoNotCallTool::class] as $tool) {
                $registry->register($app->make($tool));
            }

            foreach ([ProjectConfigTool::class, ProspectHistoryTool::class, ReportCreateTool::class, WorkflowCatalogTool::class, WorkflowDraftTool::class, WorkflowExecutionsTool::class, TrashListTool::class, TrashRestoreTool::class, ImportStatusTool::class, ImportRunTool::class, CampaignStatusTool::class, CampaignSetActiveTool::class, DocumentTemplatesTool::class, DocumentGenerateTool::class, IncomingCommunicationsTool::class, IncomingMarkTreatedTool::class, MessageTemplatesTool::class, PipelineOverviewTool::class, WorkflowCancelExecutionTool::class, ImportMappingTool::class, ImportConfigureTool::class, TeamMembersTool::class, ProjectUserMembershipTool::class, UserRolesSetTool::class, PappersSearchTool::class, ProjectEntitySaveTool::class, ProjectEntityDeleteTool::class, DocumentTemplateInstallTool::class, QuestionnaireOverviewTool::class, QuestionnaireResponsesTool::class, QuestionnaireBuildTool::class, VehiclesTool::class, CommissionsTool::class] as $tool) {
                $registry->register($app->make($tool));
            }

            // What an agent can do: declared as data in WriteCapabilities
            foreach ($app->make(WriteCapabilities::class)->tools() as $tool) {
                $registry->register($tool);
            }

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::defaultStringLength(191);
        $this->app->bind(VoiceProvider::class, ConfiguredVoiceProvider::class);

        $this->app->resolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('brevo', function (array $config) {
                return new BrevoTransport(
                    $config['api_key'],
                    $config['from_address'],
                    $config['from_name'] ?? '',
                );
            });
        });

        $this->app['validator']->resolver(function($translator, $data, $rules, $messages) {
            return new TwoFactorsTokenValidator($translator, $data, $rules, $messages);
        });
    }
}
