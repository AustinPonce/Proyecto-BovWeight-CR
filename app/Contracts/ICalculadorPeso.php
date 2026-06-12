<?php

namespace App\Contracts;

/**
 * INTERFACE DEL PATRÓN STRATEGY
 *
 * Todas las estrategias deben
 * implementar este método.
 */
interface ICalculadorPeso
{
    public function calcular(array $datos): float;
}
