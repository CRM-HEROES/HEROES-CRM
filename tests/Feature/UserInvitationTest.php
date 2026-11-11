<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\UserInvitation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Création d'utilisateur par invitation (lien, jamais de mot de passe par email),
 * mot de passe fort obligatoire, et fermeture des failles de rôle.
 * Base SQLite en mémoire : la vraie base n'est jamais touchée.
 */
class UserInvitationTest extends TestCase
{
    private const STRONG = 'Bonjour-2026-Heroes!';

    protected function setUp(): void
    {
        parent::setUp();

        // Ces middlewares écrivent dans des tables de suivi / de projet que ce test n'a pas.
        // Les middlewares "signed", "auth:sanctum" et ForcePasswordChange restent actifs.
        $this->withoutMiddleware([
            \App\Http\Middleware\UserTrackActivity::class,
            \App\Http\Middleware\UserLastProjectCheck::class,
            \App\Http\Middleware\ProjectUserImpersonateCheck::class,
            \App\Http\Middleware\ProjectUserAccessCheck::class,
            \App\Http\Middleware\ProjectUserCheck::class,
            \App\Http\Middleware\ProjectProspectCheck::class,
            \App\Http\Middleware\ProjectRoleCheck::class,
        ]);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'mail.default' => 'array',
        ]);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'Le test ne doit pas tourner sur la vraie base.');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach ((new User())->getFillable() as $column) {
                if (in_array($column, ['password', 'role', 'banned_at', 'creator_id', 'last_project_id', 'default_projects', 'two_factors_secret', 'two_factors_check_type', 'last_activity'], true)) {
                    continue;
                }
                $table->string($column)->nullable();
            }
            $table->string('password')->default('x');
            $table->string('role')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('password_temporary_expires_at')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->unsignedBigInteger('last_project_id')->nullable();
            $table->text('default_projects')->nullable();
            $table->text('two_factors_secret')->nullable();
            $table->string('two_factors_check_type')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->timestamps();
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

    // ---------- Helpers ----------

    private function makeUser(string $email, ?string $role = null, bool $pending = false, ?int $creatorId = null): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'Test',
            'email' => $email,
            'password' => Hash::make('ancien-mot-de-passe'),
            'role' => $role,
            'must_change_password' => $pending,
            'creator_id' => $creatorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($id);
    }

    private function mails()
    {
        return app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();
    }

    private function lastBody(): string
    {
        return $this->mails()->last()->getOriginalMessage()->getTextBody();
    }

    /** @return string URL de l'API d'activation, tirée du lien de l'email */
    private function activationUrl(): string
    {
        preg_match('#/invitation/(\d+)\?(\S+)#', $this->lastBody(), $m);
        $this->assertNotEmpty($m, "Aucun lien d'invitation dans l'email.");

        return url('/api/invitation/' . $m[1]) . '?' . $m[2];
    }

    private function activate(string $url, string $password = self::STRONG)
    {
        return $this->postJson($url, ['password' => $password, 'password_confirmation' => $password]);
    }

    // ---------- Règle de mot de passe fort ----------

    /**
     * @dataProvider weakPasswords
     */
    public function test_mot_de_passe_faible_est_refuse(string $password, string $missing): void
    {
        $this->assertStringContainsString($missing, implode(' | ', StrongPassword::missing($password)));
    }

    public static function weakPasswords(): array
    {
        return [
            'trop court' => ['Ab1!', 'au moins 12 caractères'],
            'sans minuscule' => ['ABCDEFGHIJK1!', 'minuscule'],
            'sans majuscule' => ['abcdefghijk1!', 'majuscule'],
            'sans chiffre' => ['Abcdefghijkl!', 'chiffre'],
            'sans symbole' => ['Abcdefghijkl1', 'symbole'],
        ];
    }

    public function test_mot_de_passe_fort_est_accepte(): void
    {
        $this->assertSame([], StrongPassword::missing(self::STRONG));
    }

    // ---------- Invitation ----------

    public function test_email_contient_un_lien_et_jamais_de_mot_de_passe(): void
    {
        $user = $this->makeUser('nouveau@example.test');

        $this->assertTrue(UserInvitation::send($user));

        $body = $this->lastBody();
        $this->assertStringContainsString('/invitation/' . $user->id . '?', $body);
        $this->assertStringContainsString('signature=', $body);
        $this->assertStringNotContainsStringIgnoringCase('mot de passe temporaire', $body);
        $this->assertStringNotContainsString('Mot de passe :', $body);
        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_activation_avec_mot_de_passe_fort(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        $user->createToken('vieux');
        UserInvitation::send($user);

        $this->activate($this->activationUrl())->assertOk()->assertJson(['success' => true]);

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check(self::STRONG, $fresh->password));
        $this->assertFalse($fresh->must_change_password);
        $this->assertNull($fresh->password_temporary_expires_at);
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_activation_refuse_un_mot_de_passe_faible_et_le_lien_reste_utilisable(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);
        $url = $this->activationUrl();

        $this->activate($url, 'faible')->assertStatus(422)->assertJsonValidationErrors('password');
        $this->assertTrue($user->fresh()->must_change_password);

        $this->activate($url)->assertOk();
    }

    public function test_confirmation_differente_est_refusee(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);

        $this->postJson($this->activationUrl(), [
            'password' => self::STRONG,
            'password_confirmation' => self::STRONG . 'x',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_lien_utilisable_une_seule_fois(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);
        $url = $this->activationUrl();

        $this->activate($url)->assertOk();
        $this->activate($url, 'Autre-Mot-De-Passe-9!')->assertStatus(410);
        $this->assertTrue(Hash::check(self::STRONG, $user->fresh()->password), 'Le mot de passe ne doit pas changer au 2e usage.');
    }

    public function test_lien_expire_est_refuse(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);
        $url = $this->activationUrl();

        $this->travel(UserInvitation::VALID_HOURS + 1)->hours();

        $this->activate($url)->assertStatus(403);
        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_lien_modifie_est_refuse(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);

        $this->activate($this->activationUrl() . 'x')->assertStatus(403);
        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_nouvelle_invitation_invalide_lancienne(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);
        $old = $this->activationUrl();

        UserInvitation::send($user->fresh());
        $new = $this->activationUrl();

        $this->activate($old)->assertStatus(410);
        $this->activate($new)->assertOk();
    }

    public function test_lien_ne_marche_pas_sur_un_compte_deja_actif(): void
    {
        $user = $this->makeUser('nouveau@example.test');
        UserInvitation::send($user);
        $url = $this->activationUrl();

        // Le compte est activé autrement entre-temps
        $user->forceFill(['must_change_password' => false])->save();

        $this->activate($url)->assertStatus(410);
    }

    public function test_mot_de_passe_egal_a_lemail_est_refuse(): void
    {
        $user = $this->makeUser('Utilisateur-Long@example.test');
        UserInvitation::send($user);

        // Même fort, il ne doit pas être identique à l'adresse email
        $this->activate($this->activationUrl(), 'Utilisateur-Long@example.test')
            ->assertStatus(422);
    }

    // ---------- Renvoi d'invitation ----------

    public function test_renvoi_impossible_sur_un_compte_actif(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');
        $active = $this->makeUser('actif@example.test', null, false);
        $hash = $active->password;

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/user/' . $active->id . '/invitation')
            ->assertStatus(422);

        $this->assertSame($hash, $active->fresh()->password, 'Le mot de passe d\'un compte actif ne doit jamais être écrasé.');
        $this->assertSame(0, $this->mails()->count());
    }

    public function test_renvoi_possible_sur_un_compte_en_attente(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');
        $pending = $this->makeUser('attente@example.test', null, true);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/user/' . $pending->id . '/invitation')
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, $this->mails()->count());
    }

    public function test_renvoi_refuse_a_un_utilisateur_sans_droit(): void
    {
        $other = $this->makeUser('autre@example.test');
        $pending = $this->makeUser('attente@example.test', null, true);

        $this->actingAs($other, 'sanctum')
            ->postJson('/api/user/' . $pending->id . '/invitation')
            ->assertStatus(404);
    }

    // ---------- Création d'utilisateur ----------

    public function test_creation_envoie_une_invitation_en_attente(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/user', ['name' => 'Nouveau', 'email' => 'nouveau@example.test'])
            ->assertCreated();

        $created = User::where('email', 'nouveau@example.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->must_change_password);
        $this->assertNull($created->role);
        $this->assertSame($admin->id, (int) $created->creator_id);
        $this->assertSame(1, $this->mails()->count());
        $this->assertStringContainsString('/invitation/' . $created->id . '?', $this->lastBody());
    }

    public function test_creation_ignore_un_mot_de_passe_envoye_par_la_requete(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/user', ['name' => 'Nouveau', 'email' => 'nouveau@example.test', 'password' => 'Imposé-123456!'])
            ->assertCreated();

        $created = User::where('email', 'nouveau@example.test')->first();
        $this->assertFalse(Hash::check('Imposé-123456!', $created->password));
    }

    public function test_creation_dun_super_admin_refusee_a_un_non_super_admin(): void
    {
        $creator = $this->makeUser('createur@example.test');

        foreach (['role', 'status'] as $field) {
            $this->actingAs($creator, 'sanctum')
                ->postJson('/api/user', ['name' => 'Pirate', 'email' => "pirate-{$field}@example.test", $field => 'super_admin'])
                ->assertStatus(403);
        }

        $this->assertSame(0, User::where('role', 'super_admin')->count());
    }

    public function test_un_super_admin_peut_creer_un_super_admin(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/user', ['name' => 'Second', 'email' => 'second@example.test', 'role' => 'super_admin'])
            ->assertCreated();

        $this->assertSame('super_admin', User::where('email', 'second@example.test')->value('role'));
    }

    public function test_creation_refuse_un_email_invalide(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/user', ['name' => 'Nouveau', 'email' => 'pas-un-email'])
            ->assertStatus(422);
    }

    // ---------- Modification d'utilisateur ----------

    public function test_on_ne_peut_pas_se_donner_le_role_super_admin(): void
    {
        $user = $this->makeUser('simple@example.test');

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/user/' . $user->id, ['name' => 'Simple', 'role' => 'super_admin'])
            ->assertOk();

        $this->assertNull($user->fresh()->role);
    }

    public function test_un_super_admin_peut_changer_un_role(): void
    {
        $admin = $this->makeUser('admin@example.test', 'super_admin');
        $user = $this->makeUser('simple@example.test');

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/user/' . $user->id, ['role' => 'super_admin'])
            ->assertOk();

        $this->assertSame('super_admin', $user->fresh()->role);
    }

    public function test_modification_refuse_un_mot_de_passe_faible(): void
    {
        $user = $this->makeUser('simple@example.test');
        $hash = $user->password;

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/user/' . $user->id, ['password' => 'faible'])
            ->assertStatus(422);

        $this->assertSame($hash, $user->fresh()->password);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/user/' . $user->id, ['password' => self::STRONG])
            ->assertOk();
        $this->assertTrue(Hash::check(self::STRONG, $user->fresh()->password));
    }

    // ---------- Changement de mot de passe (ancien flux) ----------

    public function test_changement_de_mot_de_passe_exige_un_mot_de_passe_fort(): void
    {
        $user = $this->makeUser('simple@example.test');

        $payload = ['current_password' => 'ancien-mot-de-passe', 'password' => 'Faible1234', 'password_confirmation' => 'Faible1234'];
        $this->actingAs($user, 'sanctum')->postJson('/api/password/change', $payload)
            ->assertStatus(422)->assertJsonValidationErrors('password');

        $payload = ['current_password' => 'ancien-mot-de-passe', 'password' => self::STRONG, 'password_confirmation' => self::STRONG];
        $this->actingAs($user, 'sanctum')->postJson('/api/password/change', $payload)->assertOk();
        $this->assertTrue(Hash::check(self::STRONG, $user->fresh()->password));
    }
}
