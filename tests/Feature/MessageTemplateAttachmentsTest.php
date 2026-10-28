<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessageTemplateAttachmentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'template_attachments_test',
            'database.connections.template_attachments_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Event::fake([Authenticated::class]);
        Storage::fake('local');
        $this->withoutMiddleware([
            Authenticate::class, UserTrackActivity::class, ProjectUserAccessCheck::class, UserLastProjectCheck::class,
        ]);
        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Agent');
            $table->string('role')->default('super_admin');
        });
        $schema->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->softDeletes();
        });
        $schema->create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('body');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_29_000001_add_email_fields_to_message_templates_table.php'))->up();
        DB::table('users')->insert(['id' => 1]);
        DB::table('projects')->insert([['id' => 1, 'slug' => 'test'], ['id' => 2, 'slug' => 'other']]);
        $this->actingAs(User::findOrFail(1));
        Gate::before(fn () => true);
    }

    protected function tearDown(): void
    {
        DB::purge('template_attachments_test');
        parent::tearDown();
    }

    public function test_template_subject_and_private_attachments_are_saved_without_exposing_storage_paths(): void
    {
        $response = $this->createTemplate()->assertCreated()
            ->assertJsonPath('subject', 'Notre offre')
            ->assertJsonPath('attachments.0.name', 'offre.pdf')
            ->assertJsonMissingPath('attachments.0.path');
        $template = MessageTemplate::findOrFail($response->json('id'));
        Storage::disk('local')->assertExists($template->attachments[0]['path']);
        $this->assertSame(1, $template->project_id);
        $this->getJson('/api/project/test/message-template')->assertOk()
            ->assertJsonPath('0.attachments.0.id', $template->attachments[0]['id'])
            ->assertJsonMissingPath('0.attachments.0.path');
    }

    public function test_multipart_update_can_keep_remove_and_add_files_and_delete_cleans_up_storage(): void
    {
        $id = $this->createTemplate()->json('id');
        $original = MessageTemplate::findOrFail($id)->attachments[0];
        $this->post('/api/project/test/message-template/'.$id, [
            '_method' => 'PUT', 'retained_attachment_ids' => [$original['id']],
            'attachments' => [UploadedFile::fake()->create('conditions.txt', 2, 'text/plain')],
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonCount(2, 'attachments');
        $new = MessageTemplate::findOrFail($id)->attachments[1];
        $this->putJson('/api/project/test/message-template/'.$id, [
            'subject' => 'Offre actualisée', 'retained_attachment_ids' => [$new['id']],
        ])->assertOk()->assertJsonCount(1, 'attachments')->assertJsonPath('subject', 'Offre actualisée');
        Storage::disk('local')->assertMissing($original['path']);
        Storage::disk('local')->assertExists($new['path']);
        $this->deleteJson('/api/project/test/message-template/'.$id)->assertOk();
        Storage::disk('local')->assertMissing($new['path']);
    }

    public function test_empty_retained_field_clears_all_files_but_omitting_it_preserves_them(): void
    {
        $id = $this->createTemplate()->json('id');
        $original = MessageTemplate::findOrFail($id)->attachments[0];
        $this->putJson('/api/project/test/message-template/'.$id, ['order' => 1])
            ->assertOk()->assertJsonCount(1, 'attachments');
        $this->post('/api/project/test/message-template/'.$id, [
            '_method' => 'PUT', 'retained_attachment_ids' => '',
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonCount(0, 'attachments');
        Storage::disk('local')->assertMissing($original['path']);
    }

    public function test_foreign_templates_and_unknown_retained_files_cannot_be_updated(): void
    {
        $id = $this->createTemplate()->json('id');
        $this->putJson('/api/project/other/message-template/'.$id, ['name' => 'Changed'])->assertNotFound();
        $this->putJson('/api/project/test/message-template/'.$id, [
            'retained_attachment_ids' => ['unknown-file'],
        ])->assertUnprocessable()->assertJsonValidationErrors('retained_attachment_ids.0');
        $this->assertCount(1, MessageTemplate::findOrFail($id)->attachments);
    }

    public function test_large_files_and_more_than_ten_files_are_rejected_before_storage(): void
    {
        $this->post('/api/project/test/message-template', [
            'name' => 'Large', 'body' => 'Message',
            'attachments' => [UploadedFile::fake()->create('large.pdf', 10241)],
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('attachments.0');
        $this->post('/api/project/test/message-template', [
            'name' => 'Too many', 'body' => 'Message',
            'attachments' => array_map(fn ($i) => UploadedFile::fake()->create("$i.txt", 1), range(1, 11)),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('attachments');
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    private function createTemplate()
    {
        return $this->post('/api/project/test/message-template', [
            'name' => 'Offre', 'subject' => 'Notre offre', 'body' => 'Bonjour {prospect.first_name}',
            'attachments' => [UploadedFile::fake()->create('offre.pdf', 3, 'application/pdf')],
        ], ['Accept' => 'application/json']);
    }
}
