<?php

namespace Database\Factories;

use App\Models\TipoFormato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para TipoFormato (catálogo de formatos de Reporte: PDF, Excel).
 *
 * @extends Factory<TipoFormato>
 */
class TipoFormatoFactory extends Factory
{
    protected $model = TipoFormato::class;

    public function definition(): array
    {
        return [
            'tipo' => fake()->unique()->randomElement(['PDF', 'Excel']),
        ];
    }

    public function pdf(): static
    {
        return $this->state(['tipo' => 'PDF']);
    }

    public function excel(): static
    {
        return $this->state(['tipo' => 'Excel']);
    }
}
