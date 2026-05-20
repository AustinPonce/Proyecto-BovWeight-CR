<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Factories\PesoStrategyFactory;
use App\Services\CalculadorPesoContext;

class PesajeController extends Controller
{
    public function calcularPeso(Request $request)
    {
        try {
            // DIP: El controlador ya no hace "new" de las estrategias directamente
            $strategy = PesoStrategyFactory::crear($request->tipo);

            $context = new CalculadorPesoContext($strategy);

            // Supongamos que los datos vienen del request real
            $peso = $context->calcular($request->all());

            return response()->json([
                'status' => 'success',
                'peso_estimado' => $peso
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
