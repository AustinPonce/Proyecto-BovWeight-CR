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

        // 3. Datos iniciales para Razas de Ganado (con factor_correccion — RF13)
        //
        //    El factor_correccion multiplica el peso estimado bruto por la
        //    características fisiológicas de la raza:
        //
        //      > 1.0 → raza más pesada de lo que el modelo "ve" en la imagen
        //      < 1.0 → raza más liviana de lo que el modelo estima
        //      = 1.0 → sin corrección (referencia base = Angus)
        //
        //    IMPORTANTE: estos valores son aproximaciones académicas.
        //    En producción deben calibrarse con datos de campo de Costa Rica.
        DB::table('Raza')->insert([
            ['raza' => 'Brahman',     'factor_correccion' => 1.1200], // zebuína, mayor masa muscular
            ['raza' => 'Pardo Suizo', 'factor_correccion' => 1.0300], // doble propósito, talla media-alta
            ['raza' => 'Holstein',    'factor_correccion' => 1.0800], // lechera de gran alzada
            ['raza' => 'Nelore',      'factor_correccion' => 1.0500], // zebuína, muy común en CR
            ['raza' => 'Gyr',         'factor_correccion' => 1.0400], // zebuína, talla media
            ['raza' => 'Jersey',      'factor_correccion' => 0.9200], // lechera de pequeña alzada
            ['raza' => 'Angus',       'factor_correccion' => 1.0000], // referencia base (= sin corrección)
            ['raza' => 'Simmental',   'factor_correccion' => 1.0500], // doble propósito, de gran alzada
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

        // 7. Medicamentos base para la calculadora de dosis
        DB::table('Medicamento')->insert([
            ['nombre' => 'Ivermectina',     'unidad' => 'ml',  'dosis_por_kg' => 0.02,  'descripcion' => 'Antiparasitario. 1 ml por 50 kg de peso vivo. Vía subcutánea.'],
            ['nombre' => 'Oxitetraciclina', 'unidad' => 'ml',  'dosis_por_kg' => 0.05,  'descripcion' => 'Antibiótico de amplio espectro. 5 ml por 100 kg. Vía intramuscular.'],
            ['nombre' => 'Vitamina B12',    'unidad' => 'ml',  'dosis_por_kg' => 0.0067,'descripcion' => 'Suplemento vitamínico. Dosis estándar: 2 ml por cabeza en animales < 300 kg.'],
            ['nombre' => 'Calcio Gluconato','unidad' => 'ml',  'dosis_por_kg' => 0.1,   'descripcion' => 'Para hipocalcemia. Vía endovenosa lenta. Consultar al veterinario.'],
            ['nombre' => 'Closantel',       'unidad' => 'ml',  'dosis_por_kg' => 0.025, 'descripcion' => 'Antiparasitario fasciolicida. 2.5 ml por 100 kg. Vía subcutánea.'],
        ]);
    }
}
