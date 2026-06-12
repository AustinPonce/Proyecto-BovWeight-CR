<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentosSeeder extends Seeder
{
    public function run(): void
    {
        $medicamentos = [
            ['nombre' => 'Ivermectina',     'unidad' => 'ml',  'dosis_por_kg' => 0.02,  'descripcion' => 'Antiparasitario. 1 ml por 50 kg de peso vivo. Vía subcutánea.'],
            ['nombre' => 'Oxitetraciclina', 'unidad' => 'ml',  'dosis_por_kg' => 0.05,  'descripcion' => 'Antibiótico de amplio espectro. 5 ml por 100 kg. Vía intramuscular.'],
            ['nombre' => 'Vitamina B12',    'unidad' => 'ml',  'dosis_por_kg' => 0.0067, 'descripcion' => 'Suplemento vitamínico. Dosis estándar: 2 ml por cabeza en animales < 300 kg.'],
            ['nombre' => 'Calcio Gluconato', 'unidad' => 'ml',  'dosis_por_kg' => 0.1,   'descripcion' => 'Para hipocalcemia. Vía endovenosa lenta. Consultar al veterinario.'],
            ['nombre' => 'Closantel',       'unidad' => 'ml',  'dosis_por_kg' => 0.025, 'descripcion' => 'Antiparasitario fasciolicida. 2.5 ml por 100 kg. Vía subcutánea.'],
        ];

        foreach ($medicamentos as $med) {
            DB::table('Medicamento')->updateOrInsert(
                ['nombre' => $med['nombre']],
                $med
            );
        }
    }
}
