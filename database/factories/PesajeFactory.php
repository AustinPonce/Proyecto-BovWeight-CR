<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\TipoPesaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Pesaje.
 *
 * - `peso` se genera en un rango realista para ganado bovino adulto (80-750 kg),
 *   acorde a decimal(6,2).
 * - `imagen` simula la ruta donde se guardaría la foto usada para la estimación
 *   (storage/app/public/pesajes/...). Es null en pesajes manuales o no sincronizados.
 * - `sincronizado` = 1 por defecto (registro ya enviado al servidor), según lo
 *   discutido en el levantamiento sobre captura offline en potreros sin señal.
 * - `id_tipo_pesaje` toma por defecto un registro existente del catálogo
 *   Tipo_Pesaje (sembrado por CatalogosSeeder).
 *
 * Nota: el modelo Pesaje dispara observers (CrearNotificacionObserver,
 * VerificarPesoObserver, AuditoriaPesajeObserver) al crearse/actualizarse.
 *
 * @extends Factory<Pesaje>
 */
class PesajeFactory extends Factory
{
    protected $model = Pesaje::class;

    public function definition(): array
    {
        return [
            'fecha'           => fake()->dateTimeBetween('-1 year', 'now'),
            'peso'            => fake()->randomFloat(2, 80, 750),
            'imagen'          => 'pesajes/' . fake()->uuid() . '.jpg',
            'sincronizado'    => 1,
            'arete'           => Animal::factory(),
            'id_tipo_pesaje'  => TipoPesaje::query()->where('tipo_pesaje', 'Estimación por Fotografía')->value('id_tipo_pesaje')
                ?? TipoPesaje::factory()->porFoto(),
        ];
    }

    /** Pesaje estimado por análisis de fotografía (caso principal del sistema). */
    public function porFoto(): static
    {
        return $this->state([
            'id_tipo_pesaje' => TipoPesaje::query()->where('tipo_pesaje', 'Estimación por Fotografía')->value('id_tipo_pesaje')
                ?? TipoPesaje::factory()->porFoto(),
        ]);
    }

    /** Pesaje tomado manualmente con báscula (sin foto asociada). */
    public function manual(): static
    {
        return $this->state([
            'imagen'         => null,
            'id_tipo_pesaje' => TipoPesaje::query()->where('tipo_pesaje', 'Pesaje Manual con Báscula')->value('id_tipo_pesaje')
                ?? TipoPesaje::factory()->manual(),
        ]);
    }

    /** Pesaje capturado sin conexión (potrero sin señal), pendiente de sincronizar. */
    public function pendienteSincronizar(): static
    {
        return $this->state(['sincronizado' => 0]);
    }
}
