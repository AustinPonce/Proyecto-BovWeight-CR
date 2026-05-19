<?php

namespace App\Strategies;

use App\Contracts\ICalculadorPeso;

/**
 * Estrategia de cálculo por IA.
 */
class FotoIAWeightStrategy implements ICalculadorPeso
{
    public function calcular(array $datos): float
    {
        /**
         * En un caso real:
         * - se enviaría imagen a IA
         * - TensorFlow
         * - API externa
         *
         * Falta desarrollar esta parte.
         */

        return rand(350, 600);
    }
}