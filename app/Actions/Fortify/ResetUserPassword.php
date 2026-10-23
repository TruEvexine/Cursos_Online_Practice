<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => ['required', 'string', Password::min(12), 'confirmed'],
        ])->validate();

        // Usar el enlace del correo prueba que el correo es suyo (también para alumnos invitados).
        $user->forceFill(['password' => $input['password'], 'email_verified_at' => $user->email_verified_at ?? now()])->save();
    }
}
