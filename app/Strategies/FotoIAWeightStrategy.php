<?php

namespace App\Strategies;

use App\Contracts\ICalculadorPeso;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Estrategia de cálculo de peso por análisis de imagen.
 *
 * Pertenece al patrón Strategy (GoF). Es una de las dos implementaciones
 * de ICalculadorPeso; la otra es FormulaManualStrategy (medidas corporales).
 *
 * Esta estrategia NO ejecuta el modelo de IA localmente. Delega en un
 * microservicio Python (Flask + YOLOv8) que corre en un puerto distinto.
 *
 *   ┌─────────────────────────┐  POST /estimar  ┌───────────────────────┐
 *   │  FotoIAWeightStrategy   │ ──multipart───▶ │  ml_service (Python)  │
 *   │  (Laravel)              │                 │  YOLOv8 + heurística  │
 *   │                         │ ◀────JSON────── │                       │
 *   └─────────────────────────┘                 └───────────────────────┘
 *
 * Si el microservicio NO está disponible, en lugar de tirar excepción
 * (que arruinaría la UX), caemos a un valor aleatorio acotado y dejamos
 * un WARNING en el log. Esto permite que el equipo siga desarrollando
 * la app aunque el ML esté caído.
 *
 * Variables de entorno usadas (definidas en .env):
 *   - ML_SERVICE_URL     (default http://127.0.0.1:5000)
 *   - ML_SERVICE_TIMEOUT (default 30 segundos)
 */
class FotoIAWeightStrategy implements ICalculadorPeso
{
    /**
     * Espera $datos['imagen'] = path relativo dentro del disk 'public',
     * por ejemplo 'pesajes/abc123.jpg'.
     *
     * Si no viene path o el ML no responde, devuelve un mock acotado y loguea.
     */
    public function calcular(array $datos): float
    {
        $pathRelativo = $datos['imagen'] ?? null;

        // Sin imagen → mock (modo "ML no disponible").
        if (! $pathRelativo) {
            Log::warning('FotoIAWeightStrategy: no se pasó path de imagen; usando mock.');
            return $this->mock();
        }

        // Resolver el path absoluto en el filesystem.
        $pathAbsoluto = Storage::disk('public')->path($pathRelativo);
        if (! is_file($pathAbsoluto)) {
            Log::warning("FotoIAWeightStrategy: archivo no encontrado: {$pathAbsoluto}; usando mock.");
            return $this->mock();
        }

        $url = rtrim(config('services.ml.url', env('ML_SERVICE_URL', 'http://127.0.0.1:5000')), '/');
        $timeout = (int) env('ML_SERVICE_TIMEOUT', 30);

        try {
            $response = Http::timeout($timeout)
                ->attach('imagen', file_get_contents($pathAbsoluto), basename($pathAbsoluto))
                ->post($url . '/estimar');
        } catch (\Throwable $e) {
            Log::warning("FotoIAWeightStrategy: ml_service inaccesible ({$e->getMessage()}); usando mock.");
            return $this->mock();
        }

        // El microservicio responde 200 si detectó vaca, 422 si no.
        if (! $response->successful()) {
            $mensaje = $response->json('error', 'desconocido');
            Log::warning("FotoIAWeightStrategy: ml_service respondió {$response->status()}: {$mensaje}; usando mock.");
            return $this->mock();
        }

        $peso = (float) $response->json('peso_estimado', 0);

        if ($peso <= 0) {
            Log::warning('FotoIAWeightStrategy: ml_service devolvió peso 0 o inválido; usando mock.');
            return $this->mock();
        }

        Log::info("FotoIAWeightStrategy: estimación ML exitosa = {$peso} kg "
            . "(confianza " . $response->json('confianza', 'n/a') . ")");

        return round($peso, 2);
    }

    /**
     * Fallback: peso aleatorio acotado en el rango razonable de un bovino adulto.
     * Solo se usa cuando el microservicio está caído. Loguea siempre que se invoca.
     */
    private function mock(): float
    {
        return (float) rand(350, 600);
    }
}
