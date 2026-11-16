<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

class AgentAttachmentApiTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemoryDatabase();
        $this->withoutMiddleware(\App\Http\Middleware\UserTrackActivity::class);
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_activity')->nullable();
            $table->string('ip_address')->nullable();
        });
        (require database_path('migrations/2026_11_13_000001_create_agent_message_attachments_table.php'))->up();
        Storage::fake('agent_attachments');
        Queue::fake();
    }

    public function test_attachment_extension_and_actual_mime_are_validated_and_file_size_is_limited(): void
    {
        [$user, $project, $agent] = $this->fixture('Allowed');

        $this->actingAs($user, 'sanctum')->post("/api/agent/{$agent->id}/ask", [
            'project' => $project->slug,
            'message' => 'Read this',
            'attachments' => [UploadedFile::fake()->create('payload.exe', 1, 'application/octet-stream')],
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $image = UploadedFile::fake()->image('photo.jpg');
        $mismatchedMime = new UploadedFile(
            $image->getPathname(),
            'mismatch.txt',
            'text/plain',
            UPLOAD_ERR_OK,
            true
        );

        $this->actingAs($user, 'sanctum')->post("/api/agent/{$agent->id}/ask", [
            'project' => $project->slug,
            'message' => 'Read this',
            'attachments' => [$mismatchedMime],
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->actingAs($user, 'sanctum')->post("/api/agent/{$agent->id}/ask", [
            'project' => $project->slug,
            'message' => 'Read this',
            'attachments' => [UploadedFile::fake()->create('large.txt', 10241, 'text/plain')],
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_conversation_attachments_are_only_visible_to_the_owner(): void
    {
        [$owner, $project, $agent] = $this->fixture('Owner');
        $messageFile = UploadedFile::fake()->createWithContent('notes.txt', 'A useful note.');

        $response = $this->actingAs($owner, 'sanctum')->post("/api/agent/{$agent->id}/ask", [
            'project' => $project->slug,
            'message' => 'Please read',
            'attachments' => [$messageFile],
        ], ['Accept' => 'application/json'])->assertStatus(202);

        $this->actingAs($owner, 'sanctum')->getJson('/api/agent/conversation/' . $response->json('conversation_id'))
            ->assertOk()
            ->assertJsonFragment(['kind' => 'attachment', 'text' => 'notes.txt']);

        $other = User::find($this->makeUser('Other'));
        $this->actingAs($other, 'sanctum')->getJson('/api/agent/conversation/' . $response->json('conversation_id'))->assertNotFound();
    }

    protected function fixture(string $name): array
    {
        $user = User::find($this->makeUser($name));
        $project = Project::find($this->makeProject($name . ' project', $user->id));
        $agent = Agent::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'name' => 'Agent',
            'model' => 'claude-opus-5-5',
            'effort' => 'medium',
            'autonomy' => Agent::READ_ONLY,
            'settings' => ['all_skills' => true],
            'status' => Agent::ACTIVE,
        ]);

        return [$user, $project, $agent];
    }
}
