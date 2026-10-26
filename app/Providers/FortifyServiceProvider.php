<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /** Hash bcrypt de una cadena aleatoria descartada; no corresponde a ninguna clave. */
    private const TIMING_HASH = '$2y$12$3VwuAdTkGLxsD2T5Wjaf1.EQSyPf01o15EWsS4czInDWXKrq7KskO';

    public function register(): void
    {
        // Correo inexistente al pedir recuperación: misma respuesta que el éxito, para no revelar qué correos existen.
        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, fn () => new class implements FailedPasswordResetLinkRequestResponse
        {
            public function toResponse($request)
            {
                return back()->with('status', trans('passwords.sent'));
            }
        });
    }

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Cuenta inactiva, correo inexistente o clave incorrecta: mismo mensaje genérico.
        // Siempre se compara un hash (uno desechable si no hay usuario) para no revelar por el tiempo de respuesta qué correos existen.
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->string('email')->lower())->where('is_active', true)->first();
            $valid = Hash::check((string) $request->password, $user?->password ?? self::TIMING_HASH);

            return $user && $valid ? $user : null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        // Fortify no limita estas rutas: evita usar la app para inundar buzones o probar tokens en masa.
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        $this->app->booted(function () {
            Route::getRoutes()->refreshNameLookups();
            foreach (['password.email', 'password.update'] as $name) {
                Route::getRoutes()->getByName($name)?->middleware('throttle:password-reset');
            }
        });
    }
}
