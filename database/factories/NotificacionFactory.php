<?php
namespace Database\Factories;
use App\Models\Notificacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificacionFactory extends Factory {
    protected $model = Notificacion::class;
    public function definition(): array {
        return [
            'mensaje' => $this->faker->sentence(),
            'fecha_envio' => now(),
            'id_recordatorio' => 1
        ];
    }
}