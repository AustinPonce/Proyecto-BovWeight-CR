<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Recordatorio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Recordatorio.
 *
 * Representa el recordatorio de re-pesaje pedido por Don Iván Chavarría
 * ("quiero pesar mis terneros cada mes para ver cómo van creciendo").
 *
 * @extends Factory<Recordatorio>
 */
class RecordatorioFactory extends Factory
{
    protected $model = Recordatorio::class;

    public function definition(): array
    {
        return [
            'frecuencia'   => fake()->randomElement(['Semanal', 'Quincenal', 'Mensual', 'Trimestral']),
            'fecha_inicio' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'arete'        => Animal::factory(),
        ];
    }

    public function mensual(): static
    {
        return $this->state(['frecuencia' => 'Mensual']);
    }
}
