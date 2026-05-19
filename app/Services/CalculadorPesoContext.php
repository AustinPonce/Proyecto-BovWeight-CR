<?php

namespace App\Services;

use App\Contracts\ICalculadorPeso;

/**
 * Strategy: Context
 * Usa dinámicamente
 * una estrategia.
 */
class CalculadorPesoContext
{
    protected ICalculadorPeso $strategy;

    public function __construct(ICalculadorPeso $strategy)
    {
        $this->strategy = $strategy;
    }

    public function calcular(array $datos): float
    {
        return $this->strategy->calcular($datos);
    }
}