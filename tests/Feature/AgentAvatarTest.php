<?php

namespace Tests\Feature;

use App\Agents\AgentAvatar;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CrmFixtureTestCase;

/**
 * The picture of an agent, through the whole kernel, on the real tables
 * (MySQL, rolled back) and a fake disk: AGENTS_TEST_DB=mysql.
 */
class AgentAvatarTest extends CrmFixtureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(AgentAvatar::DISK);
    }

    protected function as(string $who): static
    {
        return $this->actingAs(User::find($this->u[$who]), 'sanctum');
    }

    protected function agent(string $who = 'admin'): int
    {
        return $this->as($who)->postJson('/api/agent', ['name' => 'Lyra', 'project' => DB::table('projects')->where('id', $this->pid)->value('slug')])->assertStatus(201)->json('id');
    }

    protected function upload(int $agent, UploadedFile $file, string $who = 'admin')
    {
        return $this->as($who)->post("/api/agent/$agent/avatar", ['photo' => $file], ['Accept' => 'application/json']);
    }

    public function test_a_photo_is_resized_to_a_square_and_shown_at_a_public_address(): void
    {
        $id = $this->agent();

        $response = $this->upload($id, UploadedFile::fake()->image('moi.jpg', 800, 400))->assertOk();
        $url = $response->json('avatar_url');

        $this->assertMatchesRegularExpression('#^/api/agent-avatar/[A-Za-z0-9]{40}$#', $url);

        // The address needs no login: it is displayed in an <img>
        $this->app['auth']->forgetGuards();
        $image = $this->get($url)->assertOk();
        $this->assertSame('image/png', $image->headers->get('Content-Type'));
        $this->assertSame('nosniff', $image->headers->get('X-Content-Type-Options'));

        [$width, $height, $type] = getimagesizefromstring($image->getContent());
        $this->assertSame([AgentAvatar::SIZE, AgentAvatar::SIZE, IMAGETYPE_PNG], [$width, $height, $type]);

        // The agent shows it
        $this->as('admin')->getJson("/api/agent/$id")->assertJson(['avatar_url' => $url]);
        $this->assertSame($url, collect($this->as('admin')->getJson('/api/agent')->json())->firstWhere('id', $id)['avatar_url']);
    }

    public function test_the_original_file_is_never_kept(): void
    {
        $id = $this->agent();

        $this->upload($id, UploadedFile::fake()->image('photo-originale.png', 500, 500))->assertOk();

        $files = Storage::disk(AgentAvatar::DISK)->allFiles();
        $this->assertCount(1, $files);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}\.png$/', $files[0]);
        $this->assertStringNotContainsString('photo-originale', $files[0]);
    }

    public function test_a_new_photo_replaces_the_old_one_and_it_can_be_removed(): void
    {
        $id = $this->agent();

        $first = $this->upload($id, UploadedFile::fake()->image('a.png', 300, 300))->json('avatar_url');
        $second = $this->upload($id, UploadedFile::fake()->image('b.png', 300, 300))->json('avatar_url');

        $this->assertNotSame($first, $second);
        $this->assertCount(1, Storage::disk(AgentAvatar::DISK)->allFiles());
        $this->get($first)->assertNotFound();
        $this->get($second)->assertOk();

        $this->as('admin')->deleteJson("/api/agent/$id/avatar")->assertOk()->assertJson(['avatar_url' => null]);
        $this->assertSame([], Storage::disk(AgentAvatar::DISK)->allFiles());
        $this->assertNull(Agent::find($id)->avatar);
        $this->get($second)->assertNotFound();
    }

    /**
     * @dataProvider refusedFiles
     */
    public function test_what_is_not_a_usable_picture_is_refused_and_nothing_is_stored(string $kind): void
    {
        $id = $this->agent();

        $file = match ($kind) {
            'script renamed as image' => UploadedFile::fake()->createWithContent('photo.png', '<?php echo shell_exec($_GET["c"]); ?>'),
            'svg' => UploadedFile::fake()->createWithContent('photo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'text' => UploadedFile::fake()->createWithContent('photo.jpg', 'ceci est du texte'),
            'pdf' => UploadedFile::fake()->create('photo.pdf', 20, 'application/pdf'),
            'too heavy' => UploadedFile::fake()->image('lourde.png', 600, 600)->size(3000),
            'empty' => UploadedFile::fake()->create('vide.png', 0),
        };

        $this->upload($id, $file)->assertStatus(422);

        $this->assertSame([], Storage::disk(AgentAvatar::DISK)->allFiles());
        $this->assertNull(Agent::find($id)->avatar);
    }

    public static function refusedFiles(): array
    {
        return array_combine(
            ['script renamed as image', 'svg', 'text', 'pdf', 'too heavy', 'empty'],
            array_map(fn ($kind) => [$kind], ['script renamed as image', 'svg', 'text', 'pdf', 'too heavy', 'empty'])
        );
    }

    public function test_a_missing_file_is_refused(): void
    {
        $id = $this->agent();

        $this->as('admin')->postJson("/api/agent/$id/avatar", [])->assertStatus(422);
    }

    public function test_nobody_changes_the_picture_of_the_agent_of_somebody_else(): void
    {
        $id = $this->agent('admin');
        $this->upload($id, UploadedFile::fake()->image('a.png', 200, 200))->assertOk();
        $name = Agent::find($id)->avatar;

        $this->upload($id, UploadedFile::fake()->image('pirate.png', 200, 200), 't1')->assertNotFound();
        $this->as('t1')->deleteJson("/api/agent/$id/avatar")->assertNotFound();

        $this->assertSame($name, Agent::find($id)->avatar);
        $this->assertCount(1, Storage::disk(AgentAvatar::DISK)->allFiles());
    }

    public function test_the_public_address_cannot_be_used_to_reach_other_files(): void
    {
        Storage::disk(AgentAvatar::DISK)->put('secret.png', 'x');

        foreach (['secret', str_repeat('a', 40), str_repeat('a', 39) . '.', 'a.png'] as $token) {
            $this->get('/api/agent-avatar/' . $token)->assertNotFound();
        }

        // A path does not even reach the controller: whatever answers, it is not a file of the disk
        $response = $this->get('/api/agent-avatar/..%2F..%2F.env');
        $this->assertNotSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringNotContainsString('APP_KEY', $response->getContent());
    }

    public function test_an_unauthenticated_visitor_cannot_upload(): void
    {
        $id = $this->agent();
        $this->app['auth']->forgetGuards();

        $this->post("/api/agent/$id/avatar", ['photo' => UploadedFile::fake()->image('a.png')], ['Accept' => 'application/json'])->assertUnauthorized();
    }
}
