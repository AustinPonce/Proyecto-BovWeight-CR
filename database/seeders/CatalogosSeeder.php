<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Datos iniciales para Tipo de Usuario
        //    Los 3 roles del sistema. El id_tipo_usuario se autoincrementa:
        //      1 = Administrador (acceso total)
        //      2 = Ganadero      (gestiona sus fincas, animales y pesajes,
        //                         y también puede consultar pesajes — antes
        //                         se pensó "Comprador" como rol aparte pero
        //                         las acciones son equivalentes a Ganadero)
        //      3 = Veterinario   (asignado a fincas, dosifica medicamentos)
        DB::table('Tipo_usuario')->insert([
            ['nombre_tipo' => 'Administrador'],
            ['nombre_tipo' => 'Ganadero'],
            ['nombre_tipo' => 'Veterinario'],
        ]);

        // 2. Datos iniciales para Estado del Animal
        DB::table('Estado')->insert([
            ['estado' => 'Activo'],
            ['estado' => 'Vendido'],
            ['estado' => 'Fallecido'],
        ]);

        // 3. Datos iniciales para Razas de Ganado
        DB::table('Raza')->insert([
            ['raza' => 'Brahman'],
            ['raza' => 'Pardo Suizo'],
            ['raza' => 'Holstein'],
            ['raza' => 'Nelore'],
            ['raza' => 'Gyr'],
        ]);

        // 4. Datos iniciales para Sexo
        DB::table('Sexo')->insert([
            ['sexo' => 'Macho'],
            ['sexo' => 'Hembra'],
        ]);

        // 5. Datos iniciales para Tipo de Pesaje
        DB::table('Tipo_Pesaje')->insert([
            ['tipo_pesaje' => 'Estimación por Fotografía'],
            ['tipo_pesaje' => 'Pesaje Manual con Báscula'],
        ]);

        // 6. Datos iniciales para Tipo de Formato de Reporte
        DB::table('Tipo_Formato')->insert([
            ['tipo' => 'PDF'],
            ['tipo' => 'Excel'],
        ]);
    }
}
