<?php

namespace App\Actions\Fortify;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Registro abierto mínimo (hito DEMO, subconjunto de la Fase 8b): solo cuentas de alumno, mayores de 18.
 * La fecha de nacimiento solo se valida; no se guarda (datos mínimos). La aceptación de términos queda en la bitácora.
 */
class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        $adultBefore = now()->subYears(18)->toDateString();

        Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique(User::class)],
            'birth_date' => ['required', 'date', 'after:1900-01-01', "before_or_equal:$adultBefore"],
            'password' => ['required', 'string', Password::min(12), 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'birth_date.before_or_equal' => 'Por ahora necesitas tener 18 años o más para crear una cuenta. Si eres menor de edad, tu escuela o tu ponente pueden inscribirte.',
            'terms.accepted' => 'Para crear tu cuenta debes aceptar los términos y el aviso de privacidad.',
        ])->validate();

        $user = new User(['name' => $input['name'], 'email' => Str::lower($input['email']), 'password' => $input['password']]);
        $user->role_id = Role::where('slug', 'alumno')->value('id');
        $user->save();

        AuditLog::create(['user_id' => $user->id, 'action' => 'terms.accepted', 'entity_type' => 'users', 'entity_id' => $user->id,
            'meta' => ['version' => config('site.terms_version')], 'ip' => request()->ip()]);

        return $user;
    }
}
