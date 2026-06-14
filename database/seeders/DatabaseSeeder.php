<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder maestro. Llama a los seeders del sistema en orden lógico:
 *   1) CatalogosSeeder  — Tipo_usuario, Estado, Raza, Sexo, Tipo_Pesaje, Tipo_Formato
 *   2) UsuariosSeeder   — 4 usuarios de prueba (uno por rol)
 *
 * Se ejecuta con:  php artisan db:seed
 * O junto a las migraciones:  php artisan migrate --seed
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CatalogosSeeder::class,
            UsuariosSeeder::class,
        ]);
    }
}
