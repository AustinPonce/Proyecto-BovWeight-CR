<?php

namespace App\Http\Controllers;

use App\Factories\ReporteFactory;
use Illuminate\Http\Request;

/**
 * Cliente del patrón Factory Method.
 */
class ReporteController extends Controller
{
    /**
     * Genera reportes dinámicamente.
     */
    public function generar(Request $request)
    {
        /**
         * Tipo recibido:
         * pdf | excel
         */
        $tipo = $request->tipo;

        /**
         * Datos de prueba.
         *
         * Luego puedes traerlos
         * desde la BD.
         */
        $datos = [

            [
                'animal' => 'CR-001',
                'peso' => 450,
                'fecha' => now(),
            ],

            [
                'animal' => 'CR-002',
                'peso' => 390,
                'fecha' => now(),
            ],

        ];

        /**
         * La factory crea
         * el objeto correcto.
         */
        $reporte = ReporteFactory::crear($tipo);

        /**
         * Polimorfismo:
         * no importa si es PDF o Excel.
         */
        return $reporte->generar($datos);
    }
}
