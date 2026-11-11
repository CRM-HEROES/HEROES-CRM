<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Changement de mot de passe uniquement par email de confirmation :
 * lien à usage unique, mot de passe fort obligatoire, anciennes connexions coupées.
 * Base SQLite en mémoire : la vraie base n'est jamais touchée.
 */
class PasswordChangeByEmailTest extends TestCase
{
    private const STRONG = 'Nouveau-Mot-2026-Heroes!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\UserTrackActivity::class);

        // La connexion automatique après le changement déclenche le suivi des visites (tables absentes ici)
        Event::fake([\Illuminate\Auth\Events\Authenticated::class]);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'mail.default' => 'array',
        ]);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'Le test ne doit pas tourner sur la vraie base.');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('password_temporary_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function makeUser(bool $pending = false): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => Hash::make('ancien-mot-de-passe'),
            'must_change_password' => $pending,
            'password_temporary_expires_at' => $pending ? now()->addDay() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($id);
    }

    private function mails()
    {
        return app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();
    }

    /** Demande l'email et renvoie le jeton lu dans le lien */
    private function requestToken(): string
    {
        $this->postJson('/password/email', ['email' => 'alice@example.test'])->assertOk();

        $mail = $this->mails()->last()->getOriginalMessage();
        $this->assertSame('Changement de votre mot de passe Heroes CRM', $mail->getSubject());

        $body = $mail->getTextBody();
        $this->assertSame(1, preg_match('#/password/reset/([A-Za-z0-9]+)\?email=alice%40example\.test#', $body, $m), "Lien absent de l'email.");

        return $m[1];
    }

    private function reset(string $token, string $password = self::STRONG, ?string $confirmation = null)
    {
        return $this->postJson('/password/reset', [
            'token' => $token,
            'email' => 'alice@example.test',
            'password' => $password,
            'password_confirmation' => $confirmation ?? $password,
        ]);
    }

    public function test_email_contient_un_lien_et_ne_change_pas_le_mot_de_passe(): void
    {
        $user = $this->makeUser();
        $hash = $user->password;

        $this->requestToken();

        $this->assertSame(1, $this->mails()->count());
        $this->assertSame($hash, $user->fresh()->password, 'Le mot de passe actuel doit rester valable tant que le lien n\'est pas utilisé.');
    }

    public function test_changement_avec_mot_de_passe_fort(): void
    {
        $user = $this->makeUser();
        $user->createToken('vieux');
        $token = $this->requestToken();

        $this->reset($token)->assertOk();

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check(self::STRONG, $fresh->password));
        $this->assertSame(0, DB::table('personal_access_tokens')->count(), 'Les anciennes connexions doivent être coupées.');
    }

    /**
     * @dataProvider weakPasswords
     */
    public function test_mot_de_passe_faible_est_refuse_et_le_lien_reste_utilisable(string $weak): void
    {
        $user = $this->makeUser();
        $hash = $user->password;
        $token = $this->requestToken();

        $this->reset($token, $weak)->assertStatus(422)->assertJsonValidationErrors('password');
        $this->assertSame($hash, $user->fresh()->password);

        $this->reset($token)->assertOk();
    }

    public static function weakPasswords(): array
    {
        return [
            'trop court' => ['Ab1!'],
            'sans symbole' => ['Abcdefghijkl1'],
            'sans chiffre' => ['Abcdefghijkl!'],
            'sans majuscule' => ['abcdefghijk1!'],
            'sans minuscule' => ['ABCDEFGHIJK1!'],
            "l'ancien flux (8 caractères)" => ['password'],
        ];
    }

    public function test_confirmation_differente_est_refusee(): void
    {
        $user = $this->makeUser();
        $hash = $user->password;
        $token = $this->requestToken();

        $this->reset($token, self::STRONG, self::STRONG . 'x')->assertStatus(422);
        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_lien_utilisable_une_seule_fois(): void
    {
        $this->makeUser();
        $token = $this->requestToken();

        $this->reset($token)->assertOk();
        $this->reset($token, 'Autre-Mot-De-Passe-9!')->assertStatus(422);
    }

    public function test_mauvais_jeton_est_refuse(): void
    {
        $user = $this->makeUser();
        $hash = $user->password;
        $this->requestToken();

        $this->reset('jeton-invente')->assertStatus(422);
        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_lien_expire_est_refuse(): void
    {
        $user = $this->makeUser();
        $hash = $user->password;
        $token = $this->requestToken();

        $this->travel(61)->minutes();

        $this->reset($token)->assertStatus(422);
        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_une_seconde_demande_rapprochee_ne_renvoie_pas_dautre_email(): void
    {
        $this->makeUser();
        $this->requestToken();

        // Même réponse de succès (on ne révèle rien), mais aucun second email
        $this->postJson('/password/email', ['email' => 'alice@example.test'])->assertOk();
        $this->assertSame(1, $this->mails()->count());
    }

    public function test_adresse_inconnue_recoit_la_meme_reponse_quune_adresse_connue(): void
    {
        $this->makeUser();

        $known = $this->postJson('/password/email', ['email' => 'alice@example.test']);
        $unknown = $this->postJson('/password/email', ['email' => 'inconnu@example.test']);

        $this->assertSame($known->status(), $unknown->status());
        $this->assertSame($known->json('message'), $unknown->json('message'));
        $this->assertSame(1, $this->mails()->count(), 'Aucun email ne doit partir pour une adresse inconnue.');
    }

    public function test_trop_de_demandes_depuis_la_meme_ip_sont_bloquees(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/password/email', ['email' => "inconnu{$i}@example.test"])->assertOk();
        }

        $this->postJson('/password/email', ['email' => 'inconnu7@example.test'])->assertStatus(429);
    }

    public function test_compte_en_attente_est_active_par_le_changement(): void
    {
        $user = $this->makeUser(true);
        $token = $this->requestToken();

        $this->reset($token)->assertOk();

        $fresh = $user->fresh();
        $this->assertFalse($fresh->must_change_password);
        $this->assertNull($fresh->password_temporary_expires_at);
    }
}
