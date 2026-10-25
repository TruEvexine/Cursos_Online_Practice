<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // En producción los correos (invitaciones, recuperación) llevan tokens: nunca deben ir al log.
        if ($this->app->isProduction() && config('mail.default') === 'log') {
            throw new RuntimeException('MAIL_MAILER=log no está permitido en producción: configura un servicio de correo real.');
        }

        if ($proxies = config('app.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        Route::resourceVerbs(['create' => 'crear', 'edit' => 'editar']);

        // El tablero del director (Fase 6) usa el último acceso; se registra desde ya para no perder historial.
        Event::listen(Login::class, fn (Login $event) => $event->user->forceFill(['last_login_at' => now()])->saveQuietly());
    }
}
