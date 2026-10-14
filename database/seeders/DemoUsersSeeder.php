<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Un usuario de prueba por rol: {rol}@demo.test con la clave DEMO_PASSWORD del .env.
 * Re-ejecutable: actualiza los existentes en vez de duplicarlos.
 */
class DemoUsersSeeder extends Seeder
{
    public const USERS = [
        'super_admin' => ['superadmin@demo.test', 'Sofía Admin (prueba)'],
        'director' => ['director@demo.test', 'Diego Director (prueba)'],
        'ponente' => ['ponente@demo.test', 'Paula Ponente (prueba)'],
        'alumno' => ['alumno@demo.test', 'Andrés Alumno (prueba)'],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Los usuarios de prueba no se crean en producción.');
        }

        $password = config('app.demo_password');
        if (blank($password) || strlen($password) < 8) {
            throw new RuntimeException('Define DEMO_PASSWORD en .env (mínimo 8 caracteres).');
        }

        $hash = Hash::make($password);

        foreach (self::USERS as $slug => [$email, $name]) {
            User::withTrashed()->firstOrNew(['email' => $email])->forceFill([
                'name' => $name,
                'password' => $hash,
                'role_id' => Role::where('slug', $slug)->value('id'),
                'is_active' => true,
                'email_verified_at' => now(),
                'deleted_at' => null,
            ])->save();
        }
    }
}
