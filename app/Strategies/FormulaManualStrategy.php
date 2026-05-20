<?php

namespace App\Strategies;

use App\Contracts\ICalculadorPeso;

class FormulaManualStrategy implements ICalculadorPeso
{
    public function calcular(array $datos): float
    {
        // Usamos nombres genéricos o validamos que existan para no romper el flujo
        $largo = $datos['longitud'] ?? 0; 
        $torax = $datos['torax'] ?? 0;

        if ($torax == 0 || $largo == 0) {
            return 0.0;
        }

        // Fórmula: (P. Torácico² * Largo) / 10840
        $peso = (($torax * $torax) * $largo) / 10840;

        return round($peso, 2);
    }
}