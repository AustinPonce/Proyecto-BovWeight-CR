<?php

namespace App\Strategies;

use App\Contracts\ICalculadorPeso;
use Illuminate\Support\Facades\Http;

class FotoIAWeightStrategy implements ICalculadorPeso
{
    public function calcular(array $datos): float
    {
        // SRP: Esta clase solo se comunica con el servicio de ML
        // Se asume que $datos['imagen'] trae la ruta o base64
        $urlServicioIA = "http://localhost:5000/predict"; 

        try {
            /* // Esto sería la implementación real con el microservicio Python
            $response = Http::post($urlServicioIA, [
                'image' => $datos['foto_path'] ?? null
            ]);
            return (float) $response->json('estimated_weight');
            */
            
            // Por ahora, simulamos el peso para la entrega actual
            return (float) rand(400, 550); 

        } catch (\Exception $e) {
            return 0.0;
        }
    }
}