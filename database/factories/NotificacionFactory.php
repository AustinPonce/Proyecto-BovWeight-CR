<?php

namespace Database\Factories;

use App\Models\Notificacion;
use App\Models\Recordatorio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Notificacion.
 *
 * Cada notificación está asociada a un Recordatorio (p. ej. aviso de que
 * toca volver a pesar un animal). `mensaje` simula el texto que recibiría
 * el ganadero en su teléfono.
 *
 * @extends Factory<Notificacion>
 */
class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    public function definition(): array
    {
        return [
            'mensaje' => fake()->randomElement([
                'Es momento de volver a pesar a tu animal.',
                'Recordatorio: registra el pesaje mensual de tus terneros.',
                'Han pasado 30 días desde el último pesaje de este animal.',
            ]),
            'fecha_envio' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'id_recordatorio' => Recordatorio::factory(),
        ];
    }
}
