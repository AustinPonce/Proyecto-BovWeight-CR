<?php

namespace Database\Factories;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Estado (catálogo de estados del Animal: Activo, Vendido, Fallecido).
 *
 * @extends Factory<Estado>
 */
class EstadoFactory extends Factory
{
    protected $model = Estado::class;

    public function definition(): array
    {
        return [
            'estado' => fake()->unique()->randomElement([
                'Activo',
                'Vendido',
                'Fallecido',
            ]),
        ];
    }

    public function activo(): static
    {
        return $this->state(['estado' => 'Activo']);
    }

    public function vendido(): static
    {
        return $this->state(['estado' => 'Vendido']);
    }

    public function fallecido(): static
    {
        return $this->state(['estado' => 'Fallecido']);
    }
}
