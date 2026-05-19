<?php

namespace App\Strategies;

use App\Contracts\ICalculadorPeso;

/**
 * Estrategia de cálculo manual.
 */
class FormulaManualStrategy implements ICalculadorPeso
{
    /**
     * Calcula peso usando medidas corporales y una fórmula manual
     */
    public function calcular(array $datos): float
    {
        $largo =
            $datos['largo_cuerpo'] ?? 0;

        $altura =
            $datos['altura'] ?? 0;

        $torax =
            $datos['perimetro_toracico'] ?? 0;

        $peso =
            (($torax * $torax) * $largo)
            / 10840;

        /**
         * Redondeamos resultado.
         */
        return round($peso, 2);
    }
}