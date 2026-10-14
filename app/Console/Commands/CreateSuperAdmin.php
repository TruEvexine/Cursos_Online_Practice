<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin';

    protected $description = 'Crea un super admin pidiendo nombre, correo y clave por consola';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Nombre'),
            'email' => strtolower((string) $this->ask('Correo')),
            'password' => $this->secret('Clave (mínimo 12 caracteres)'),
        ];
        $data['password_confirmation'] = $this->secret('Repite la clave');

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        (new User)->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => Role::where('slug', 'super_admin')->value('id'),
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info("Super admin {$data['email']} creado.");

        return self::SUCCESS;
    }
}
