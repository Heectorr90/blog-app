<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear roles necesarios
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'lector']); // ¡ESTE ES CRUCIAL!

        // Si tu UserSeeder crea un admin, puedes asignarle el rol aquí:
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
