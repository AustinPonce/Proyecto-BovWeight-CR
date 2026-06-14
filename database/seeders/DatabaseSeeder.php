<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Recordatorio;
use App\Models\Notificacion;
use App\Models\Reporte;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // Carga los catálogos primero
        $this->call([CatalogosSeeder::class]);

        // Ganadero de prueba fijo
        $ganadero = Usuario::factory()->create([
            'cedula' => '101110111',
            'nombre' => 'Productor Ganadero UCR',
            'correo' => 'ganadero@ucr.ac.cr',
            'id_tipo_usuario' => 2
        ]);

        // Veterinario de prueba fijo
        $veterinario = Usuario::factory()->create([
            'cedula' => '202220222',
            'id_tipo_usuario' => 3
        ]);

        // Crea la Finca vinculada a la cédula fija
        $finca = Finca::factory()->create([
            'id_finca' => 1,
            'cedula' => $ganadero->cedula
        ]);

        // Vincula el veterinario a la finca en la tabla pivot
        $finca->veterinarios()->attach($veterinario->cedula);

        // Crea los animales dentro de la finca
        $animales = Animal::factory()->count(3)->create([
            'id_finca' => $finca->id_finca
        ]);

        foreach ($animales as $animal) {
            Pesaje::factory()->create(['arete' => $animal->arete]);
            
            $rec = Recordatorio::factory()->create(['arete' => $animal->arete]);
            
            Notificacion::factory()->create(['id_recordatorio' => $rec->id_recordatorio]);
        }

        // Crea el Reporte y le asocia los animales creados
        $reporte = Reporte::factory()->create(['id_reporte' => 1]);
        $reporte->animales()->attach($animales->pluck('arete')->toArray());
    }
}