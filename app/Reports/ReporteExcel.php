<?php

namespace App\Reports;

use App\Contracts\IReporte;
use App\Exports\ReporteExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Clase encargada de generar Excel. Implementa la interfaz IReporte.
 */
class ReporteExcel implements IReporte
{
    public function generar(array $datos)
    {
        return Excel::download(new ReporteExport($datos), 'reporte_pesaje.xlsx');
    }
}