<?php

namespace Database\Factories;

use App\Models\Reporte;
use App\Models\TipoFormato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Reporte.
 *
 * `id_Tipo_Formato` toma por defecto un registro existente del catálogo
 * Tipo_Formato (PDF/Excel, sembrado por CatalogosSeeder), acorde al pedido
 * del comprador Don Diego Chavarría de recibir reportes "presentables"
 * (PDF) con los animales y pesos estimados.
 *
 * @extends Factory<Reporte>
 */
class ReporteFactory extends Factory
{
    protected $model = Reporte::class;

    public function definition(): array
    {
        return [
            'fecha_generacion' => fake()->dateTimeBetween('-3 months', 'now'),
            'id_Tipo_Formato' => TipoFormato::query()->inRandomOrder()->value('id_Tipo_Formato')
                ?? TipoFormato::factory(),
        ];
    }

    public function pdf(): static
    {
        return $this->state([
            'id_Tipo_Formato' => TipoFormato::query()->where('tipo', 'PDF')->value('id_Tipo_Formato')
                ?? TipoFormato::factory()->pdf(),
        ]);
    }

    public function excel(): static
    {
        return $this->state([
            'id_Tipo_Formato' => TipoFormato::query()->where('tipo', 'Excel')->value('id_Tipo_Formato')
                ?? TipoFormato::factory()->excel(),
        ]);
    }
}
