<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Gèneres (llista tancada de l'MVP)
        $this->call(GenreSeeder::class);

        // Compte d'administració (la contrasenya es defineix a ADMIN_PASSWORD del .env)
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@unshelf.test')],
            [
                'name' => 'Administració',
                'password' => env('ADMIN_PASSWORD', 'canvia-aquesta-contrasenya'),
                'city' => 'Barcelona',
            ]
        );
        $admin->role = 'admin';   // fora de $fillable: s'assigna explícitament
        $admin->save();

        // Usuari de proves
        User::firstOrCreate(
            ['email' => 'test@unshelf.test'],
            [
                'name' => 'Usuari de proves',
                'password' => 'password',
                'city' => 'Barcelona',
            ]
        );
    }
}
