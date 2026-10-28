<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class ProspectEmailAttachmentsTest extends TestCase
{
    protected array $sent = [];
    protected string $attachmentId;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'prospect_email_test',
            'database.connections.prospect_email_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Storage::fake('local');
        Event::fake([Authenticated::class]);
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
            $table->string('name')->default('CRM');
            $table->softDeletes();
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value');
        });
        $schema->create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('attachments')->nullable();
        });

        DB::table('users')->insert(['id' => 1]);
        DB::table('projects')->insert([['id' => 1, 'slug' => 'test'], ['id' => 2, 'slug' => 'other']]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'email' => 'alice@example.com', 'first_name' => 'Alice', 'meta' => json_encode(['company' => 'Alpha'])],
            ['id' => 2, 'project_id' => 1, 'email' => 'bob@example.com', 'first_name' => 'Bob', 'meta' => null],
            ['id' => 3, 'project_id' => 2, 'email' => 'other@example.com', 'first_name' => 'Other', 'meta' => null],
            ['id' => 4, 'project_id' => 1, 'email' => null, 'first_name' => 'No email', 'meta' => null],
        ]);
        DB::table('project_settings')->insert([
            'project_id' => 1, 'key' => 'email', 'value' => json_encode([
                'api_key' => 'test-key', 'from' => ['address' => 'sender@example.com', 'name' => 'CRM'],
            ]),
        ]);
        $this->attachmentId = (string) Str::uuid();
        Storage::disk('local')->put('message-templates/1/proposal.pdf', '%PDF-1.4 proposal');
        DB::table('message_templates')->insert([
            'id' => 1, 'project_id' => 1, 'name' => 'Proposal', 'body' => 'Hello',
            'attachments' => json_encode([[
                'id' => $this->attachmentId, 'name' => 'proposal.pdf', 'path' => 'message-templates/1/proposal.pdf',
                'type' => 'application/pdf', 'size' => 17,
            ]]),
        ]);
        DB::table('message_templates')->insert([
            'id' => 2, 'project_id' => 2, 'name' => 'Foreign template', 'body' => 'Other', 'attachments' => '[]',
        ]);

        Mail::shouldReceive('purge')->andReturnNull();
        Mail::shouldReceive('send')->andReturnUsing(function ($view, $data, $callback) {
            $message = new Message(new Email());
            $callback($message);
            $this->sent[] = ['view' => $view, 'data' => $data, 'email' => $message->getSymfonyMessage()];
        });
        $this->actingAs(User::query()->findOrFail(1));
    }

    protected function tearDown(): void
    {
        DB::purge('prospect_email_test');
        parent::tearDown();
    }

    public function test_email_attaches_uploaded_and_template_files_and_personalizes_fields(): void
    {
        $this->post($this->endpoint(), array_merge($this->payload(), [
            'subject' => 'Projet de {prospect.first_name}',
            'body' => 'Bonjour {prospect.first_name}, société {prospect.meta.company}.',
            'template_id' => 1,
            'template_attachment_ids' => [$this->attachmentId],
            'attachments' => [UploadedFile::fake()->createWithContent('notes.txt', 'Notes du rendez-vous')],
        ]), ['Accept' => 'application/json'])->assertOk()->assertJson(['sent' => true]);

        $this->assertCount(1, $this->sent);
        $message = $this->sent[0];
        $this->assertSame('alice@example.com', $message['email']->getTo()[0]->getAddress());
        $this->assertSame('Projet de Alice', $message['email']->getSubject());
        $this->assertSame('Bonjour Alice, société Alpha.', $message['data']['body']);
        $attachments = $message['email']->getAttachments();
        $this->assertCount(2, $attachments);
        $this->assertSame('Notes du rendez-vous', $attachments[0]->getBody());
        $this->assertSame('%PDF-1.4 proposal', $attachments[1]->getBody());
        $this->assertSame('proposal.pdf', $attachments[1]->getPreparedHeaders()->get('Content-Disposition')->getParameter('filename'));
    }

    public function test_plain_json_email_keeps_support_for_the_existing_composer(): void
    {
        $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJson(['sent' => true]);
        $this->assertCount(1, $this->sent);
        $this->assertCount(0, $this->sent[0]['email']->getAttachments());
    }

    public function test_bulk_sends_each_project_recipient_an_individually_personalized_email_with_attachments(): void
    {
        $this->post('/api/project/test/prospect/email/bulk', array_merge($this->payload(), [
            'prospects' => [1, 2, 3, 4], 'body' => 'Bonjour {prospect.first_name}',
            'template_id' => 1, 'template_attachment_ids' => [$this->attachmentId],
            'attachments' => [UploadedFile::fake()->createWithContent('notes.txt', 'Notes')],
        ]), ['Accept' => 'application/json'])->assertOk()->assertJson(['sent' => 2]);

        $this->assertCount(2, $this->sent);
        $this->assertSame(['Bonjour Alice', 'Bonjour Bob'], array_column(array_column($this->sent, 'data'), 'body'));
        $recipients = [];
        foreach ($this->sent as $message) {
            $this->assertCount(1, $message['email']->getTo());
            $this->assertCount(2, $message['email']->getAttachments());
            $recipients[] = $message['email']->getTo()[0]->getAddress();
        }
        $this->assertSame(['alice@example.com', 'bob@example.com'], $recipients);
    }

    public function test_template_files_can_be_removed_from_the_composed_email(): void
    {
        $this->postJson($this->endpoint(), array_merge($this->payload(), ['template_id' => 1]))->assertOk();
        $this->assertCount(0, $this->sent[0]['email']->getAttachments());
    }

    /** @dataProvider invalidTemplateSelections */
    public function test_foreign_unknown_and_incomplete_template_selections_are_rejected(string $case): void
    {
        $selection = ['template_id' => 1, 'template_attachment_ids' => [$this->attachmentId]];
        if ($case === 'foreign') {
            $selection['template_id'] = 2;
        } elseif ($case === 'unknown_id') {
            $selection['template_attachment_ids'] = [(string) Str::uuid()];
        } elseif ($case === 'missing_template') {
            unset($selection['template_id']);
        } else {
            Storage::disk('local')->delete('message-templates/1/proposal.pdf');
        }
        $this->postJson($this->endpoint(), array_merge($this->payload(), $selection))->assertUnprocessable();
        $this->assertCount(0, $this->sent);
    }

    public static function invalidTemplateSelections(): array
    {
        return [['foreign'], ['unknown_id'], ['missing_template'], ['missing_file']];
    }

    /** @dataProvider invalidFiles */
    public function test_invalid_attachment_counts_and_sizes_are_rejected_before_sending(string $case): void
    {
        $data = $this->payload();
        if ($case === 'combined') {
            $data['attachments'] = array_map(fn () => UploadedFile::fake()->create('notes.txt', 1), range(1, 10));
            $data['template_id'] = 1;
            $data['template_attachment_ids'] = [$this->attachmentId];
        } elseif ($case === 'size') {
            $data['attachments'] = [UploadedFile::fake()->create('large.pdf', 10241)];
        } else {
            $data['attachments'] = ['not an uploaded file'];
        }
        $this->post($this->endpoint(), $data, ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertCount(0, $this->sent);
    }

    public static function invalidFiles(): array
    {
        return [['combined'], ['size'], ['file']];
    }

    public function test_bulk_without_any_valid_project_recipient_is_rejected(): void
    {
        $this->postJson('/api/project/test/prospect/email/bulk', array_merge($this->payload(), [
            'prospects' => [3, 4],
        ]))->assertUnprocessable()->assertJsonValidationErrors('prospects');
        $this->assertCount(0, $this->sent);
    }

    protected function endpoint(): string
    {
        return '/api/project/test/prospect/1/email';
    }

    protected function payload(): array
    {
        return ['to' => 'alice@example.com', 'subject' => 'Projet', 'body' => 'Bonjour'];
    }
}
