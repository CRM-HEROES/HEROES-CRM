<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\IncomingCommunicationsTool;
use App\Agents\Tools\IncomingMarkTreatedTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use App\Services\IncomingCommunications;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

/**
 * Communications received: the numbers, what waits, and "Demande traité", for the AI agents.
 */
class IncomingCommunicationsToolsTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $project = new Project();
        $project->id = 1;
        $owner = new User();
        $owner->id = 42;
        $this->context = new AgentExecutionContext($owner, $project);
    }

    protected function readTool(?callable $configure = null): IncomingCommunicationsTool
    {
        $service = Mockery::mock(IncomingCommunications::class);
        $configure && $configure($service);

        return new IncomingCommunicationsTool($service);
    }

    protected function markTool(array $json = ['marked' => 2, 'count' => 3, 'sms' => 1, 'email' => 2, 'voicemail' => 0], int $status = 200): IncomingMarkTreatedTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data) use ($json, $status) {
            $this->sent[] = [$method, $path, $data];

            return new InternalResponse($status, $json, json_encode($json));
        });

        return new IncomingMarkTreatedTool($api);
    }

    public function test_the_count_is_the_one_of_the_badge_for_the_owner_of_the_agent(): void
    {
        $tool = $this->readTool(fn ($service) => $service->shouldReceive('counts')->once()
            ->withArgs(fn ($project, $userId) => $project->id === 1 && $userId === 42)
            ->andReturn(['count' => 3, 'sms' => 2, 'email' => 1, 'voicemail' => 0]));

        $result = $tool->handle($this->context, ['view' => 'count']);

        $this->assertFalse($result->isError);
        $this->assertSame(3, $result->data['count']);
    }

    public function test_the_pending_list_is_asked_for_the_owner_with_the_prospect_and_limit(): void
    {
        $tool = $this->readTool(fn ($service) => $service->shouldReceive('pending')->once()
            ->withArgs(fn ($project, $userId, $prospectId, $limit) => $userId === 42 && $prospectId === 7 && $limit === 5)
            ->andReturn(['total' => 1, 'prospects' => [['id' => 7, 'name' => 'Marie Curie', 'pending' => ['sms' => [11], 'email' => [], 'voicemail' => []]]]]));

        $result = $tool->handle($this->context, ['view' => 'pending', 'prospect_id' => 7, 'limit' => 5]);

        $this->assertSame([11], $result->data['prospects'][0]['pending']['sms']);
    }

    public function test_a_prospect_without_anything_to_treat_is_not_an_error(): void
    {
        $tool = $this->readTool(fn ($service) => $service->shouldReceive('pending')->andReturn(['total' => 0, 'prospects' => []]));

        $result = $tool->handle($this->context, ['view' => 'pending', 'prospect_id' => 7]);

        $this->assertFalse($result->isError);
        $this->assertSame(0, $result->data['total']);
    }

    public function test_the_history_needs_a_prospect_and_hides_an_unknown_one(): void
    {
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->readTool()->handle($this->context, ['view' => 'history'])->code);

        $tool = $this->readTool(fn ($service) => $service->shouldReceive('readHistory')->andThrow(new ModelNotFoundException()));
        $this->assertSame(ToolResult::NOT_FOUND, $tool->handle($this->context, ['view' => 'history', 'prospect_id' => 9])->code);
    }

    public function test_the_history_gives_who_and_when_but_not_the_message(): void
    {
        $tool = $this->readTool(fn ($service) => $service->shouldReceive('readHistory')->andReturn(collect([
            (object) ['id' => 11, 'message' => 'Texte privé', 'user_id' => 3, 'read_at' => '2026-11-17 10:00:00', 'user_name' => 'Léa'],
        ])));

        $result = $tool->handle($this->context, ['view' => 'history', 'prospect_id' => 7]);

        $this->assertSame([['id' => 11, 'treated_by' => 'Léa', 'treated_at' => '2026-11-17 10:00:00']], $result->data['history']);
        $this->assertStringNotContainsString('privé', json_encode($result->data));
    }

    public function test_marking_goes_through_the_route_with_clean_identifiers(): void
    {
        $result = $this->markTool()->handle($this->context, ['prospect_id' => 7, 'type' => 'sms', 'ids' => [11, 11, 12, 0, -3]]);

        $this->assertFalse($result->isError);
        $this->assertSame([['POST', 'incoming-communications/read', ['prospect_id' => 7, 'type' => 'sms', 'ids' => [11, 12]]]], $this->sent);
        $this->assertSame(2, $result->data['marked']);
        $this->assertSame(0, $result->data['already_treated_or_not_found']);
        $this->assertSame(3, $result->data['remaining']['count']);
    }

    public function test_what_was_already_treated_is_told(): void
    {
        $result = $this->markTool(['marked' => 1, 'count' => 0])->handle($this->context, ['prospect_id' => 7, 'type' => 'email', 'ids' => [1, 2, 3]]);

        $this->assertSame(1, $result->data['marked']);
        $this->assertSame(2, $result->data['already_treated_or_not_found']);
    }

    public function test_the_cap_the_confirmation_and_the_errors_of_the_crm(): void
    {
        $tool = $this->markTool();

        $this->assertSame(2, $tool->affectedCount($this->context, ['ids' => [1, 1, 2, 0]]));
        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $tool->handle($this->context, ['prospect_id' => 7, 'type' => 'sms', 'ids' => range(1, IncomingMarkTreatedTool::MAX + 1)])->code);
        $this->assertSame(ToolResult::VALIDATION_FAILED, $tool->handle($this->context, ['prospect_id' => 7, 'type' => 'sms', 'ids' => []])->code);

        $notFound = $this->markTool(['message' => 'No query results'], 404)->handle($this->context, ['prospect_id' => 7, 'type' => 'sms', 'ids' => [1]]);
        $this->assertSame(ToolResult::NOT_FOUND, $notFound->code);
    }

    public function test_the_risks(): void
    {
        $this->assertSame(IncomingCommunicationsTool::READ, $this->readTool()->risk());
        $this->assertSame(IncomingMarkTreatedTool::WRITE, $this->markTool()->risk());
    }
}
