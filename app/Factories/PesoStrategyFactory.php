<?php

namespace App\Factories;

use App\Contracts\ICalculadorPeso;
use App\Strategies\FormulaManualStrategy;
use App\Strategies\FotoIAWeightStrategy;

class PesoStrategyFactory
{
    /**
     * SRP: Esta clase solo se encarga de elegir la estrategia.
     * OCP: Si hay una nueva, solo tocas este archivo, no el controlador.
     */
    public static function crear(string $tipo): ICalculadorPeso
    {
        return match ($tipo) {
            'manual' => new FormulaManualStrategy(),
            'foto'   => new FotoIAWeightStrategy(),
            default  => throw new \Exception("Tipo de pesaje no soportado"),
        };
    }
}
