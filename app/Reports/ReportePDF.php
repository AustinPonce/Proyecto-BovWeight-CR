<?php

namespace App\Reports;

use App\Contracts\IReporte;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Clase encargada de generar PDFs. Implementa la interfaz IReporte.
 */
class ReportePDF implements IReporte
{
    public function generar(array $datos)
    {
        $pdf = Pdf::loadView('reports.reporte_pdf', compact('datos'));

        return $pdf->download('reporte_pesaje.pdf');
    }
}