<?php

namespace Database\Factories;

use App\Models\Finca;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Finca.
 *
 * Cada finca pertenece a un Usuario con rol Ganadero (campo `cedula`).
 * Por defecto crea un ganadero nuevo vía UsuarioFactory::ganadero().
 *
 * Inspirado en los casos reales del levantamiento: "Finca La Esperanza, Liberia"
 * y "Finca Las Palmas, Bagaces" (cantones de Guanacaste).
 *
 * @extends Factory<Finca>
 */
class FincaFactory extends Factory
{
    protected $model = Finca::class;

    public function definition(): array
    {
        return [
            'nombre'    => 'Finca ' . fake()->unique()->lastName(),
            'ubicacion' => fake()->randomElement([
                'Liberia, Guanacaste',
                'Bagaces, Guanacaste',
                'Cañas, Guanacaste',
                'Nicoya, Guanacaste',
                'Santa Cruz, Guanacaste',
            ]),
            'cedula' => Usuario::factory()->ganadero(),
        ];
    }
}
