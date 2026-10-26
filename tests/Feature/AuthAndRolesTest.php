<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthAndRolesTest extends TestCase
{
    use DatabaseTransactions;

    public static function roles(): array
    {
        return [
            'super admin' => ['super_admin', 'Super administrador'],
            'director' => ['director', 'Director'],
            'ponente' => ['ponente', 'Ponente'],
            'alumno' => ['alumno', 'Alumno'],
        ];
    }

    #[DataProvider('roles')]
    public function test_each_role_logs_in_and_sees_its_role(string $slug, string $roleName): void
    {
        $user = User::factory()->role($slug)->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->followingRedirects()->get('/dashboard')
            ->assertOk()
            ->assertSee('Hola, '.$user->name)
            ->assertSee($roleName);
    }

    public static function areas(): array
    {
        // [rol, rutas permitidas]
        return [
            'super admin' => ['super_admin', ['/admin', '/direccion']],
            'director' => ['director', ['/direccion']],
            'ponente' => ['ponente', ['/ponente']],
            'alumno' => ['alumno', ['/alumno']],
        ];
    }

    #[DataProvider('areas')]
    public function test_each_role_only_reaches_its_own_area(string $slug, array $allowed): void
    {
        $user = User::factory()->role($slug)->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect($allowed[0]);

        foreach (['/admin', '/direccion', '/ponente', '/alumno'] as $path) {
            $this->actingAs($user)->get($path)->assertStatus(in_array($path, $allowed, true) ? 200 : 403);
        }
    }

    public function test_user_deactivated_mid_session_is_logged_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/alumno')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->get('/alumno')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_unverified_user_must_verify_email_first(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/alumno')->assertRedirect('/email/verify');
        $this->actingAs($user)->get('/perfil')->assertRedirect('/email/verify');
        $this->get('/email/verify')->assertOk()->assertSee('Verifica tu correo');
    }

    public function test_login_records_last_login_at(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_password_reset_requests_are_rate_limited(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/forgot-password', ['email' => "nadie{$i}@demo.test"])->assertRedirect();
        }

        $this->post('/forgot-password', ['email' => 'otro@demo.test'])->assertTooManyRequests();
        $this->post('/reset-password', [])->assertTooManyRequests(); // cuota compartida por IP
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_wrong_password_and_unknown_email_get_the_same_message(): void
    {
        $user = User::factory()->create();

        $generic = ['email' => 'El correo o la contraseña no son correctos.'];

        $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])->assertSessionHasErrors($generic);
        $this->post('/login', ['email' => 'nadie@demo.test', 'password' => 'incorrecta'])->assertSessionHasErrors($generic);
        $this->assertGuest();
    }


    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertTooManyRequests();
        $this->assertGuest();
    }

    public function test_demo_seeder_creates_one_user_per_role_and_is_rerunnable(): void
    {
        config(['app.demo_password' => 'clave-de-prueba-123']);

        $this->seed(DemoUsersSeeder::class);
        $this->seed(DemoUsersSeeder::class);

        foreach (DemoUsersSeeder::USERS as $slug => [$email]) {
            $this->assertSame($slug, User::where('email', $email)->sole()->role->slug);
        }
    }

    public function test_create_super_admin_command(): void
    {
        $this->artisan('app:create-super-admin')
            ->expectsQuestion('Nombre', 'Admin Consola')
            ->expectsQuestion('Correo', 'Root@Demo.test')
            ->expectsQuestion('Clave (mínimo 12 caracteres)', 'una-clave-larga-123')
            ->expectsQuestion('Repite la clave', 'una-clave-larga-123')
            ->assertSuccessful();

        $this->assertSame('super_admin', User::where('email', 'root@demo.test')->sole()->role->slug);
    }
}
