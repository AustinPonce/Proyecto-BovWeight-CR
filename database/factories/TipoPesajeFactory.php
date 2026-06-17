<?php

namespace Database\Factories;

use App\Models\TipoPesaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para TipoPesaje (catálogo: Estimación por Fotografía / Pesaje Manual con Báscula).
 *
 * @extends Factory<TipoPesaje>
 */
class TipoPesajeFactory extends Factory
{
    protected $model = TipoPesaje::class;

    public function definition(): array
    {
        return [
            'tipo_pesaje' => fake()->unique()->randomElement([
                'Estimación por Fotografía',
                'Pesaje Manual con Báscula',
            ]),
        ];
    }

    public function porFoto(): static
    {
        return $this->state(['tipo_pesaje' => 'Estimación por Fotografía']);
    }

    public function manual(): static
    {
        return $this->state(['tipo_pesaje' => 'Pesaje Manual con Báscula']);
    }
}
