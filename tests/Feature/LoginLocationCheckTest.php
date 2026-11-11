<?php

namespace Tests\Feature;

use App\Http\Controllers\API\LoginLocationController;
use App\Models\User;
use App\Services\LoginLocationCheck;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;
use Tests\TestCase;

/**
 * Vérification du pays de connexion : blocage tant que non confirmé,
 * expiration des liens, confirmation et refus.
 * Base SQLite en mémoire : la vraie base n'est jamais touchée.
 */
class LoginLocationCheckTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Le suivi des visites écrit dans des tables que ce test n'a pas : seul ce middleware
        // est coupé, la vérification de signature des liens (middleware "signed") reste active.
        $this->withoutMiddleware(\App\Http\Middleware\UserTrackActivity::class);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'mail.default' => 'array',
            'login_location.block' => true,
            'login_location.link_ttl_minutes' => 60,
        ]);
        DB::purge('sqlite');

        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'Le test ne doit pas tourner sur la vraie base.');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->default('x');
            $table->string('role')->nullable();
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

        (require database_path('migrations/2026_11_05_082344_create_user_login_locations_table.php'))->up();

        $id = DB::table('users')->insertGetId([
            'name' => 'Alice', 'email' => 'alice@example.test', 'password' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->user = User::find($id);

        Location::shouldReceive('get')->andReturnUsing(function () {
            if ($this->position instanceof \Throwable) {
                throw $this->position;
            }

            return $this->position;
        });
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    /** @var Position|bool|\Throwable */
    private $position = false;

    private function fromCountry(string $country, ?string $city = 'Ville'): void
    {
        $this->position = new Position();
        $this->position->countryName = $country;
        $this->position->cityName = $city;
    }

    private function sentMails(): int
    {
        return count(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages());
    }

    private function statuses(): array
    {
        return DB::table('user_login_locations')->orderBy('id')->pluck('status')->all();
    }

    private function signed(string $action, int $id, $expires)
    {
        $prefix = Route::has('api.login-location.confirm') ? 'api.' : '';

        return URL::temporarySignedRoute($prefix . 'login-location.' . $action, $expires, ['id' => $id]);
    }

    public function test_premiere_connexion_est_acceptee_et_devient_la_reference(): void
    {
        $this->fromCountry('France');

        $this->assertFalse(LoginLocationCheck::check($this->user, '1.1.1.1'));
        $this->assertSame(['known'], $this->statuses());
        $this->assertSame(0, $this->sentMails());
    }

    public function test_meme_pays_nest_pas_bloque(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->assertFalse(LoginLocationCheck::check($this->user, '1.1.1.2'));
        $this->assertSame(['known'], $this->statuses());
    }

    public function test_nouveau_pays_est_bloque_et_un_email_est_envoye(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->fromCountry('Japon', 'Tokyo');
        $this->assertTrue(LoginLocationCheck::check($this->user, '2.2.2.2'));

        $this->assertSame(['known', 'pending'], $this->statuses());
        $this->assertSame(1, $this->sentMails());
    }

    public function test_deuxieme_tentative_reste_bloquee_sans_second_email(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $this->assertTrue(LoginLocationCheck::check($this->user, '2.2.2.2'));

        $this->assertSame(1, $this->sentMails());
        $this->assertSame(['known', 'pending'], $this->statuses());
    }

    public function test_apres_confirmation_la_connexion_passe(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');

        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');
        $this->get($this->signed('confirm', $id, now()->addMinutes(10)))->assertOk();

        $this->assertSame(['known', 'confirmed'], $this->statuses());
        $this->assertFalse(LoginLocationCheck::check($this->user, '2.2.2.2'));
    }

    public function test_lien_expire_est_refuse_puis_nouvelle_tentative_renvoie_un_email(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');
        $link = $this->signed('confirm', $id, now()->addMinutes(60));

        $this->travel(61)->minutes();

        // Le lien signé a expiré : refusé, rien n'est confirmé
        $this->get($link)->assertStatus(403);
        $this->assertSame(['known', 'pending'], $this->statuses());

        // Se reconnecter : toujours bloqué, ancien lien fermé, nouvel email
        $this->assertTrue(LoginLocationCheck::check($this->user, '2.2.2.2'));
        $this->assertSame(['known', 'expired', 'pending'], $this->statuses());
        $this->assertSame(2, $this->sentMails());
    }

    public function test_confirmation_refusee_si_la_ligne_a_depasse_le_delai_meme_avec_un_lien_valide(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');

        DB::table('user_login_locations')->where('id', $id)->update(['created_at' => now()->subHours(2)]);

        $this->get($this->signed('confirm', $id, now()->addMinutes(10)))->assertStatus(410);
        $this->assertSame(['known', 'pending'], $this->statuses());
    }

    public function test_refus_deconnecte_et_supprime_les_jetons(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $this->user->createToken('HeroesCRM');
        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');

        $this->get($this->signed('deny', $id, now()->addMinutes(10)))->assertOk();

        $this->assertSame(['known', 'denied'], $this->statuses());
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
        $this->assertTrue(LoginLocationCheck::check($this->user, '2.2.2.2'), 'Un pays refusé doit redemander une confirmation.');
    }

    public function test_lien_deja_utilise_est_refuse(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');
        $link = $this->signed('confirm', $id, now()->addMinutes(10));

        $this->get($link)->assertOk();
        $this->get($link)->assertStatus(410);
    }

    public function test_lien_modifie_est_refuse(): void
    {
        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');

        $this->get($this->signed('confirm', $id, now()->addMinutes(10)) . 'x')->assertStatus(403);
        $this->assertSame(['known', 'pending'], $this->statuses());
    }

    public function test_blocage_desactive_laisse_passer_mais_envoie_lemail(): void
    {
        config(['login_location.block' => false]);

        $this->fromCountry('France');
        LoginLocationCheck::check($this->user, '1.1.1.1');
        $this->fromCountry('Japon');

        $this->assertFalse(LoginLocationCheck::check($this->user, '2.2.2.2'));
        $this->assertSame(1, $this->sentMails());
    }

    public function test_localisation_impossible_ne_bloque_pas(): void
    {
        $this->position = false;

        $this->assertFalse(LoginLocationCheck::check($this->user, '3.3.3.3'));
        $this->assertSame([], $this->statuses());
    }

    public function test_erreur_technique_ne_bloque_pas(): void
    {
        $this->position = new \RuntimeException('service en panne');

        $this->assertFalse(LoginLocationCheck::check($this->user, '3.3.3.3'));
    }

    public function test_reponse_de_blocage(): void
    {
        $response = LoginLocationCheck::blockedResponse();

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('location_confirmation_required', $response->getData()->code);
    }

    public function test_meme_pays_mais_autre_ville_est_bloque(): void
    {
        $this->fromCountry('France', 'Paris');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->fromCountry('France', 'Lyon');
        $this->assertTrue(LoginLocationCheck::check($this->user, '2.2.2.2'));
        $this->assertSame(['known', 'pending'], $this->statuses());
        $this->assertSame(1, $this->sentMails());
    }

    public function test_meme_ville_ecrite_autrement_nest_pas_bloquee(): void
    {
        $this->fromCountry('France', 'Paris');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->fromCountry('France', 'PARIS');
        $this->assertFalse(LoginLocationCheck::check($this->user, '1.1.1.2'));
    }

    public function test_ville_inconnue_se_rabat_sur_le_pays(): void
    {
        $this->fromCountry('France', 'Paris');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->fromCountry('France', null);
        $this->assertFalse(LoginLocationCheck::check($this->user, '1.1.1.2'));
    }

    public function test_ville_confirmee_reste_acceptee_apres_un_retour_a_la_ville_dorigine(): void
    {
        $this->fromCountry('France', 'Paris');
        LoginLocationCheck::check($this->user, '1.1.1.1');

        $this->fromCountry('France', 'Lyon');
        LoginLocationCheck::check($this->user, '2.2.2.2');
        $id = DB::table('user_login_locations')->where('status', 'pending')->value('id');
        $this->get($this->signed('confirm', $id, now()->addMinutes(10)))->assertOk();

        // Paris puis Lyon à nouveau : aucune nouvelle confirmation
        $this->fromCountry('France', 'Paris');
        $this->assertFalse(LoginLocationCheck::check($this->user, '1.1.1.1'));
        $this->fromCountry('France', 'Lyon');
        $this->assertFalse(LoginLocationCheck::check($this->user, '2.2.2.2'));
        $this->assertSame(1, $this->sentMails());
    }
}
