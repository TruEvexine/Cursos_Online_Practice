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

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Has iniciado sesión como')
            ->assertSee($roleName);
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

    public function test_public_registration_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
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
