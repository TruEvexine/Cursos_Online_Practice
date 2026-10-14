<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_alumno_can_only_edit_the_limited_fields(): void
    {
        $alumno = User::factory()->role('alumno')->create(['name' => 'Nombre Original']);

        $this->actingAs($alumno)->put('/perfil', [
            'name' => 'Nombre Cambiado',
            'headline' => 'Hacker',
            'website_url' => 'https://ejemplo.com',
            'bio' => 'Me gusta aprender.',
            'interests' => 'música, código',
            'accent_color' => '#15803D',
            'role_id' => 1,
        ])->assertRedirect('/perfil')->assertSessionHasNoErrors();

        $alumno->refresh();
        $this->assertSame('Nombre Original', $alumno->name);
        $this->assertSame('alumno', $alumno->role->slug);
        $this->assertSame('Me gusta aprender.', $alumno->profile->bio);
        $this->assertSame('#15803D', $alumno->profile->accent_color);
        $this->assertNull($alumno->profile->headline);
        $this->assertNull($alumno->profile->website_url);
    }

    public function test_ponente_can_edit_the_full_profile(): void
    {
        $ponente = User::factory()->role('ponente')->create();

        $this->actingAs($ponente)->put('/perfil', [
            'name' => 'Paula Pérez',
            'headline' => 'Profesora de matemáticas',
            'website_url' => 'https://paula.example.com',
        ])->assertSessionHasNoErrors();

        $ponente->refresh();
        $this->assertSame('Paula Pérez', $ponente->name);
        $this->assertSame('Profesora de matemáticas', $ponente->profile->headline);
    }

    public function test_website_must_be_https_and_color_must_be_hex(): void
    {
        $ponente = User::factory()->role('ponente')->create();

        $this->actingAs($ponente)->put('/perfil', [
            'name' => 'X',
            'website_url' => 'javascript:alert(1)',
            'accent_color' => 'red;}',
        ])->assertSessionHasErrors(['website_url', 'accent_color']);

        $this->actingAs($ponente)->put('/perfil', ['name' => 'X', 'website_url' => 'http://inseguro.com'])
            ->assertSessionHasErrors('website_url');
    }

    public function test_avatar_is_reencoded_privately_with_a_random_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/perfil', [
            'avatar' => UploadedFile::fake()->image('mi foto.png', 1200, 800),
        ])->assertSessionHasNoErrors();

        $path = $user->fresh()->profile->avatar_path;
        $this->assertMatchesRegularExpression('#^avatars/[A-Za-z0-9]{40}\.webp$#', $path);
        Storage::disk('local')->assertExists($path);
        $this->assertSame([512, 341], array_slice(getimagesizefromstring(Storage::disk('local')->get($path)), 0, 2));

        $this->actingAs(User::factory()->create())->get("/usuarios/{$user->id}/foto")->assertOk();
    }

    public function test_replacing_the_avatar_deletes_the_old_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/perfil', ['avatar' => UploadedFile::fake()->image('a.jpg')]);
        $old = $user->fresh()->profile->avatar_path;

        $this->actingAs($user)->put('/perfil', ['avatar' => UploadedFile::fake()->image('b.jpg')]);

        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($user->fresh()->profile->avatar_path);
    }

    public function test_dangerous_or_fake_images_are_rejected(): void
    {
        $user = User::factory()->create();

        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->actingAs($user)->put('/perfil', ['avatar' => $svg])->assertSessionHasErrors('avatar');

        $fakePng = UploadedFile::fake()->createWithContent('x.png', '<?php echo "hola";');
        $this->actingAs($user)->put('/perfil', ['avatar' => $fakePng])->assertSessionHasErrors('avatar');

        $this->actingAs($user)->put('/perfil', ['avatar' => UploadedFile::fake()->image('big.jpg')->size(3000)])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->profile);
    }

    public function test_guests_cannot_see_profiles_or_avatars(): void
    {
        $user = User::factory()->create();

        $this->get('/perfil')->assertRedirect('/login');
        $this->get("/usuarios/{$user->id}/foto")->assertRedirect('/login');
    }

    public function test_profile_page_renders_for_each_role(): void
    {
        foreach (['super_admin', 'director', 'ponente', 'alumno'] as $slug) {
            $this->actingAs(User::factory()->role($slug)->create())
                ->get('/perfil')->assertOk()->assertSee('Mi perfil');
        }
    }
}
