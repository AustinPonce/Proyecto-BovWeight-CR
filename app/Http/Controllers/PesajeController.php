<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\CalculadorPesoContext;

use App\Strategies\FormulaManualStrategy;
use App\Strategies\FotoIAWeightStrategy;

/**
 * CLIENTE DEL PATRÓN STRATEGY
 */
class PesajeController extends Controller
{
    /**
     * Calcula peso dinámicamente.
     */
    public function calcularPeso(Request $request)
    {
        /**
         * Tipo de pesaje:
         * manual | foto
         */
        $tipo = $request->tipo;

        /**
         * Datos simulados.
         */
        $datos = [

            'torax' => 180,

            'longitud' => 150

        ];

        /**
         * Selección dinámica
         * de estrategia.
         */
        switch ($tipo) {

            case 'manual':

                $strategy =
                    new FormulaManualStrategy();

            break;

            case 'foto':

                $strategy =
                    new FotoIAWeightStrategy();

            break;

            default:

                return response()->json([
                    'error' => 'Tipo inválido'
                ], 400);
        }

        /**
         * Context usa estrategia.
         */
        $context =
            new CalculadorPesoContext(
                $strategy
            );

        /**
         * Ejecuta cálculo.
         */
        $peso =
            $context->calcular($datos);

        return response()->json([

            'tipo' => $tipo,

            'peso_estimado' => $peso

        ]);
    }
}