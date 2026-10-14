<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_known_and_unknown_emails_get_the_same_answer(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $sent = 'Si el correo tiene una cuenta, te enviamos un enlace para restablecer tu contraseña.';

        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')->assertSessionHas('status', $sent);
        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nadie@demo.test'])
            ->assertRedirect('/forgot-password')->assertSessionHas('status', $sent)->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
        Notification::assertCount(1);
    }

    public function test_user_can_reset_password_with_the_emailed_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $this->get('/reset-password/'.$n->token.'?email='.$user->email)->assertOk();

            $this->post('/reset-password', [
                'token' => $n->token,
                'email' => $user->email,
                'password' => 'una-clave-nueva-123',
                'password_confirmation' => 'una-clave-nueva-123',
            ])->assertRedirect('/login')->assertSessionHasNoErrors();

            return true;
        });

        $this->assertTrue(Hash::check('una-clave-nueva-123', $user->fresh()->password));
    }

    public function test_short_new_password_is_rejected(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $this->post('/reset-password', [
                'token' => $n->token,
                'email' => $user->email,
                'password' => 'corta',
                'password_confirmation' => 'corta',
            ])->assertSessionHasErrors('password');

            return true;
        });
    }
}
