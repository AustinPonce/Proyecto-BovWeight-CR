# ADR-003 — Patrón Strategy para estimación de peso

**Estado:** Aceptada
**Fecha:** 2026-06-08
**Decisores:** equipo BovWeight CR (sub-equipo SOLID)

## Contexto

Hay (al menos) dos formas de estimar el peso de un animal:

1. **Manual**: el ganadero mide largo del cuerpo, altura y perímetro torácico
   con una cinta. Se aplica una fórmula matemática conocida:
   `peso = (perímetro² × largo) / 10840`.

2. **Por fotografía**: se envía una foto al servicio ML que estima el peso
   usando YOLOv8 + heurística (ADR-002).

Es probable que en el futuro aparezcan otras estrategias (modelo entrenado
específicamente, estimación por video, integración con balanza electrónica).

## Decisión

Aplicar el patrón **Strategy** (Gang of Four):

- Interfaz: `App\Contracts\ICalculadorPeso` con un solo método `calcular(array $datos): float`.
- Implementaciones: `FormulaManualStrategy`, `FotoIAWeightStrategy`.
- Context: `App\Services\CalculadorPesoContext` que recibe la strategy y delega.
- Cliente: `PesajeController::store` elige la strategy según el `tipo` del request.

## Consecuencias

✅ Agregar una estrategia nueva = 1 clase nueva + un `case` en `resolverStrategy()`.
   No se toca el controller ni el modelo.

✅ Cada strategy es testeable de forma aislada (mock del context).

✅ Cumple el Open/Closed Principle (abierto a extensión, cerrado a modificación).

⚠️ Para tipos hardcoded (`'manual'`, `'foto'`) hay un `match` en el controller.
   Si crece a 5+ estrategias, considerar mover a un Factory (ver `ReporteFactory`
   por analogía).
