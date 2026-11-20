<?php

namespace Tests\Feature;

use App\Models\Line;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Sms;
use App\Notifications\Brevo as BrevoNotification;
use App\Notifications\CloudTalk as CloudTalkNotification;
use App\Notifications\MTarget as MTargetNotification;
use App\Notifications\SMSBOX as SmsboxNotification;
use App\Notifications\Ultramsg as UltramsgNotification;
use App\Observers\SmsObserver;
use App\Services\SMS\Brevo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * Which operator an SMS goes to, and what happens when the operator is not configured.
 *
 * What is proven here is the CRM side: the source of an SMS chooses the operator, an unconfigured operator leaves the reason
 * on the SMS (and never a notification), and the Brevo call has the right shape. The calls of SMSBOX, MTarget and
 * Ultramsg use cURL directly: they cannot be faked here and still need a real test with the operator.
 */
class SmsOperatorsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected int $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Notification::fake();

        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('mobile_phone_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->text('message')->nullable();
            $table->string('source')->nullable();
            $table->boolean('from_user')->default(true);
            $table->boolean('is_incoming')->default(false);
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
        });
        Schema::create('project_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type');
            $table->text('log');
            $table->string('date')->nullable();
        });
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('operator');
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->owner = $this->makeUser('Alice');
        $this->pid = $this->makeProject('Alpha', $this->owner);
        DB::table('prospects')->insert(['id' => 1, 'project_id' => $this->pid, 'mobile_phone_number' => '+33612345678', 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function sms(?string $source): Sms
    {
        $id = DB::table('sms')->insertGetId(['prospect_id' => 1, 'creator_id' => $this->owner, 'message' => 'Bonjour', 'source' => $source, 'from_user' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $sms = Sms::find($id);
        $prospect = Prospect::withoutGlobalScopes()->find(1);
        $prospect->setRelation('project', Project::withoutGlobalScopes()->find($this->pid));
        $sms->setRelation('prospect', $prospect);

        return $sms;
    }

    /** What the observer does when an SMS from a user is created */
    protected function send(Sms $sms): void
    {
        $observer = new SmsObserver();
        $method = new \ReflectionMethod($observer, 'send');
        $method->setAccessible(true);
        $method->invoke($observer, $sms);
    }

    protected function line(string $name, int $user, array $config): void
    {
        DB::table('lines')->insert(['project_id' => $this->pid, 'name' => $name, 'operator' => 'cloudtalk', 'user_id' => $user, 'config' => json_encode($config), 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function configure(string $key): void
    {
        DB::table('project_settings')->insert(['project_id' => $this->pid, 'key' => $key, 'value' => json_encode(['token' => 'x']), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_each_configured_operator_gets_the_sms_of_its_source(): void
    {
        $operators = [
            'smsbox' => SmsboxNotification::class,
            'ultramsg' => UltramsgNotification::class,
            'mtarget' => MTargetNotification::class,
            'brevo' => BrevoNotification::class,
        ];

        foreach ($operators as $source => $notification) {
            $this->configure($source);

            $this->send($this->sms($source));

            Notification::assertSentTo(Prospect::withoutGlobalScopes()->find(1), $notification);
        }
    }

    public function test_an_operator_that_is_not_configured_leaves_its_reason_on_the_sms_and_sends_nothing(): void
    {
        foreach (['smsbox' => 'SMSBOX', 'ultramsg' => 'Ultramsg', 'mtarget' => 'MTarget', 'brevo' => 'Brevo'] as $source => $name) {
            $sms = $this->sms($source);

            $this->send($sms);

            $this->assertStringStartsWith("$name:", (string) $sms->fresh()->error, $source);
        }

        Notification::assertNothingSent();
        $this->assertSame(4, DB::table('project_logs')->where('type', 'error')->count());
    }

    public function test_cloudtalk_needs_a_line_with_a_number_for_the_user_who_sends(): void
    {
        $withoutLine = $this->sms('cloudtalk');
        $this->send($withoutLine);
        $this->assertStringContainsString('CloudTalk', (string) $withoutLine->fresh()->error);
        Notification::assertNothingSent();

        // A line of another user, and a line without number, do not count
        $this->line('Autre', $this->owner + 99, ['numero' => '+33100000000']);
        $this->line('Sans numéro', $this->owner, []);
        $this->send($this->sms('cloudtalk'));
        Notification::assertNothingSent();

        $this->line('Ma ligne', $this->owner, ['numero' => '+33100000001']);
        $this->send($this->sms('cloudtalk'));
        Notification::assertSentTo(Prospect::withoutGlobalScopes()->find(1), CloudTalkNotification::class);
    }

    public function test_a_source_the_crm_does_not_know_is_told_on_the_sms(): void
    {
        $sms = $this->sms('pigeon');

        $this->send($sms);

        $this->assertNotEmpty($sms->fresh()->error);
        Notification::assertNothingSent();
    }

    public function test_a_source_that_only_records_the_sms_sends_nothing_and_raises_no_error(): void
    {
        foreach (['ringover', 'whatsapp', 'telephone'] as $source) {
            $sms = $this->sms($source);

            $this->send($sms);

            $this->assertNull($sms->fresh()->error, $source);
        }
        Notification::assertNothingSent();
    }

    public function test_the_brevo_call_has_the_right_shape(): void
    {
        config(['brevo.api_key' => 'cle-brevo', 'brevo.sender' => 'HEROES', 'brevo.api_uri' => 'https://api.brevo.test/v3/transactionalSMS/send']);
        Http::fake(['api.brevo.test/*' => Http::response(['messageId' => 987654], 201)]);

        $id = (new Brevo())->sendSMS('+33 6 12 34 56 78', 'Bonjour Jean');

        $this->assertSame(987654, $id);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.brevo.test/v3/transactionalSMS/send'
                && $request->hasHeader('api-key', 'cle-brevo')
                && $request['sender'] === 'HEROES'
                && $request['content'] === 'Bonjour Jean'
                && $request['type'] === 'transactional'
                // International format, digits only: what Brevo expects
                && $request['recipient'] === '33612345678';
        });
    }

    public function test_brevo_receives_a_local_number_as_typed_digits_without_a_country_code(): void
    {
        // Documented on purpose: the service only strips the signs. A number stored as "06 12 34 56 78" reaches Brevo as
        // 0612345678, which an operator that wants the international format may refuse.
        config(['brevo.api_key' => 'cle-brevo', 'brevo.sender' => 'HEROES', 'brevo.api_uri' => 'https://api.brevo.test/v3/transactionalSMS/send']);
        Http::fake(['api.brevo.test/*' => Http::response(['messageId' => 1], 201)]);

        (new Brevo())->sendSMS('06 12 34 56 78', 'Bonjour');

        Http::assertSent(fn ($request) => $request['recipient'] === '0612345678');
    }

    public function test_a_brevo_error_is_raised_with_the_message_of_the_operator(): void
    {
        config(['brevo.api_key' => 'cle-brevo', 'brevo.sender' => 'HEROES', 'brevo.api_uri' => 'https://api.brevo.test/v3/transactionalSMS/send']);
        Http::fake(['api.brevo.test/*' => Http::response(['message' => 'Not enough credits'], 402)]);

        $this->expectExceptionMessage('Not enough credits');

        (new Brevo())->sendSMS('0612345678', 'Bonjour');
    }

    public function test_brevo_refuses_to_call_without_key_sender_message_or_number(): void
    {
        Http::fake();

        foreach ([['', 'HEROES', '0612345678', 'x'], ['k', '', '0612345678', 'x'], ['k', 'HEROES', '0612345678', ''], ['k', 'HEROES', '', 'x']] as [$key, $sender, $number, $message]) {
            config(['brevo.api_key' => $key, 'brevo.sender' => $sender]);

            try {
                (new Brevo())->sendSMS($number, $message);
                $this->fail('Une exception était attendue');
            } catch (\Exception) {
                $this->addToAssertionCount(1);
            }
        }
        Http::assertNothingSent();
    }
}
