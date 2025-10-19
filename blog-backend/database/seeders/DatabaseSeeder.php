<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Llamar al Seeder de Roles y Permisos (¡CRUCIAL QUE VAYA PRIMERO!)
        $this->call([
            // Asegúrate de que los roles y permisos se creen primero (para evitar el error 500)
            RolePermissionSeeder::class,
        ]);

        // 2. Crear usuarios o cualquier otra data que dependa de los roles (opcional)
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
            // Opcional: Asignarle el rol de admin al usuario de prueba
        ])->assignRole('admin');

        // 3. Llamar a otros seeders si es necesario
        $this->call([
            CategorySeeder::class,
            ArticleSeeder::class,
        ]);
    }
}
