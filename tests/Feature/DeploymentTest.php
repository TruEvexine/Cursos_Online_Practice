<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Middleware\TrustProxies;
use RuntimeException;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        TrustProxies::flushState();
        parent::tearDown();
    }

    private function bootWith(array $config): void
    {
        config($config);
        (new AppServiceProvider($this->app))->boot();
    }

    private function failLogin(string $email, string $clientIp)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2', 'HTTP_X_FORWARDED_FOR' => $clientIp])
            ->post('/login', ['email' => $email, 'password' => 'incorrecta']);
    }

    public function test_login_throttle_uses_the_client_ip_from_x_forwarded_for_behind_a_trusted_proxy(): void
    {
        $this->bootWith(['app.trusted_proxies' => '*']);
        $email = User::factory()->create()->email;

        foreach (range(1, 5) as $_) {
            $this->failLogin($email, '203.0.113.5')->assertRedirect();
        }
        $this->failLogin($email, '203.0.113.5')->assertStatus(429);
        // Otra persona detrás del mismo proxy no queda bloqueada.
        $this->failLogin($email, '198.51.100.7')->assertRedirect();
    }

    public function test_without_trusted_proxies_x_forwarded_for_is_ignored(): void
    {
        $email = User::factory()->create()->email;

        foreach (range(1, 5) as $_) {
            $this->failLogin($email, '203.0.113.5');
        }
        $this->failLogin($email, '198.51.100.7')->assertStatus(429);
    }

    public function test_production_refuses_to_boot_with_the_log_mailer(): void
    {
        $this->app['env'] = 'production';

        try {
            $this->expectException(RuntimeException::class);
            $this->bootWith(['mail.default' => 'log']);
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    public function test_security_headers(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    }
}
