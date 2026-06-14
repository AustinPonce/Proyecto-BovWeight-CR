<?php
namespace Database\Factories;
use App\Models\Reporte;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReporteFactory extends Factory {
    protected $model = Reporte::class;
    public function definition(): array {
        return [
            'fecha_generacion' => now(),
            'id_tipo_formato' => 1
        ];
    }
}