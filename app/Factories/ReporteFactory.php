<?php

namespace App\Factories;

use App\Contracts\IReporte;
use App\Reports\ReportePDF;
use App\Reports\ReporteExcel;
use Exception;

/**
 * FACTORY METHOD
 *
 * Se encarga de decidir
 * qué tipo de reporte crear.
 */
class ReporteFactory
{
    /**
     * @throws Exception
     */
    public static function crear(string $tipo): IReporte
    {
        $tipo = strtolower($tipo);

        return match ($tipo) {
            'pdf' => new ReportePDF(),
            'excel' => new ReporteExcel(),
            default => throw new Exception('Formato de reporte inválido')
        };
    }
}