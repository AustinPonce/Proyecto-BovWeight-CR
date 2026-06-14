<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosSeeder extends Seeder {
    public function run(): void {
        DB::table('Tipo_usuario')->insert([
            ['id_tipo_usuario' => 1, 'nombre_tipo' => 'Administrador'],
            ['id_tipo_usuario' => 2, 'nombre_tipo' => 'Ganadero'],
            ['id_tipo_usuario' => 3, 'nombre_tipo' => 'Veterinario'],
        ]);

        DB::table('Estado')->insert([
            ['id_estado' => 1, 'estado' => 'Activo'],
            ['id_estado' => 2, 'estado' => 'Vendido'],
        ]);

        DB::table('Raza')->insert([
            ['id_raza' => 1, 'raza' => 'Brahman'],
            ['id_raza' => 2, 'raza' => 'Nelore'],
        ]);

        DB::table('Sexo')->insert([
            ['id_sexo' => 1, 'sexo' => 'Macho'],
            ['id_sexo' => 2, 'sexo' => 'Hembra'],
        ]);

        DB::table('Tipo_Pesaje')->insert([
            ['id_tipo_pesaje' => 1, 'tipo_pesaje' => 'Control Mensual'],
        ]);

        DB::table('Tipo_Formato')->insert([
            ['id_Tipo_Formato' => 1, 'tipo' => 'PDF'],
        ]);
    }
}