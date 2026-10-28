<?php

namespace Tests\Feature;

use App\Exceptions\BrevoTransportException;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProspectEmailDeliveryErrorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.debug' => true,
            'database.default' => 'prospect_email_errors_test',
            'database.connections.prospect_email_errors_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
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
            $table->string('email');
            $table->softDeletes();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value');
        });
        DB::table('users')->insert(['id' => 1]);
        DB::table('projects')->insert(['id' => 1, 'slug' => 'test']);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'email' => 'alice@example.com'],
            ['id' => 2, 'project_id' => 1, 'email' => 'bob@example.com'],
        ]);
        DB::table('project_settings')->insert([
            'project_id' => 1, 'key' => 'email', 'value' => json_encode([
                'api_key' => 'test-key', 'from' => ['address' => 'sender@example.com', 'name' => 'CRM'],
            ]),
        ]);
        Mail::shouldReceive('purge')->andReturnNull();
        $this->actingAs(User::query()->findOrFail(1));
    }

    protected function tearDown(): void
    {
        DB::purge('prospect_email_errors_test');
        parent::tearDown();
    }

    /** @dataProvider endpoints */
    public function test_brevo_rejection_returns_validation_error_without_a_debug_trace(string $endpoint, array $extra): void
    {
        $message = 'Brevo bloque l’adresse IP 41.74.210.228. Autorisez-la dans les IP autorisées.';
        Mail::shouldReceive('send')->once()->andThrow(new BrevoTransportException($message));

        $response = $this->postJson($endpoint, array_merge($this->payload(), $extra))
            ->assertUnprocessable()->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', $message)
            ->assertJsonMissingPath('exception')->assertJsonMissingPath('trace');

        $this->assertStringNotContainsString('test-key', $response->getContent());
    }

    public static function endpoints(): array
    {
        return [
            ['/api/project/test/prospect/1/email', []],
            ['/api/project/test/prospect/email/bulk', ['prospects' => [1, 2]]],
        ];
    }

    public function test_unrelated_programming_errors_are_not_misrepresented_as_brevo_validation_errors(): void
    {
        $this->withoutExceptionHandling();
        Mail::shouldReceive('send')->once()->andThrow(new \LogicException('Unexpected programming failure'));
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unexpected programming failure');

        $this->postJson('/api/project/test/prospect/1/email', $this->payload());
    }

    protected function payload(): array
    {
        return ['to' => 'alice@example.com', 'subject' => 'Projet', 'body' => 'Bonjour'];
    }
}
