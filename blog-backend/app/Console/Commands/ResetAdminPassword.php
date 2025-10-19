<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {email=test@example.com} {password=password123}';
    protected $description = 'Resetear contraseña de un usuario';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario con email {$email} no encontrado");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Contraseña actualizada para: {$email}");
        $this->info("Nueva contraseña: {$password}");

        // Verificar
        if (Hash::check($password, $user->password)) {
            $this->info("Verificación exitosa");
        } else {
            $this->error("Error en la verificación");
        }

        return 0;
    }
}
