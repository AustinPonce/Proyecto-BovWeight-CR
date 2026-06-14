<?php
namespace Database\Factories;
use App\Models\Recordatorio;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordatorioFactory extends Factory {
    protected $model = Recordatorio::class;
    public function definition(): array {
        return [
            'frecuencia' => 'Mensual',
            'fecha_inicio' => now(),
            'arete' => 'CR-XXXXX'
        ];
    }
}