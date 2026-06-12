<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando el microservicio ML procesa una imagen pero NO detecta
 * un animal bovino. La diferencia con un fallo de red (en el cual sí caemos
 * a un mock para que la app siga funcionando) es que acá la imagen llegó al
 * modelo y el modelo respondió "esto no es una vaca". Si silenciáramos eso
 * con un peso aleatorio estaríamos mintiéndole al usuario.
 *
 * El controller atrapa esta excepción y muestra el mensaje al usuario:
 * "no se detectó una vaca; se detectó una persona con 89% de confianza".
 *
 * Requerimiento académico #2 (PRIORIDAD ALTA): el sistema NO debe estimar
 * peso si no detecta ganado bovino.
 */
class NoBovinoDetectadoException extends RuntimeException
{
    /**
     * @param  string  $mensajeUsuario  Mensaje legible para mostrar al usuario.
     * @param  array  $detalles  Datos opcionales del diagnóstico
     *                           (ej. ['clase' => 'horse', 'confianza' => 0.87]).
     */
    public function __construct(
        public readonly string $mensajeUsuario,
        public readonly array $detalles = []
    ) {
        parent::__construct($mensajeUsuario);
    }
}
