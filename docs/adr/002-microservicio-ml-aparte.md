# ADR-002 — Microservicio ML separado (Python + Flask)

**Estado:** Aceptada
**Fecha:** 2026-06-12
**Decisores:** equipo BovWeight CR

## Contexto

El sistema debe estimar el peso de un animal a partir de una fotografía. Esto
requiere computer vision (detección de objetos + estimación). El ecosistema
maduro para esto es Python (PyTorch, Ultralytics, OpenCV).

## Restricciones del curso

El enunciado exige (sección 5):
> El componente de estimación de peso debe implementarse como un
> microservicio en Python (Flask + YOLOv8 o similar).

## Decisión

Implementar el componente de estimación como **proceso HTTP separado**:
- Servicio: Flask 3 + Ultralytics YOLOv8 + Pillow.
- Comunicación: HTTP POST multipart/form-data desde Laravel.
- Stateless: ningún acceso a la BD del backend.
- Tolerante a fallo: si el servicio está caído, Laravel cae a un mock con
  warning en el log (no rompe la experiencia del usuario).

## Alternativas consideradas

1. **Ejecutar el modelo dentro de PHP** (TensorFlow PHP, o un binding nativo).
   ❌ Inviable: PHP no tiene bindings maduros para PyTorch/YOLO.
   ❌ Rompería la restricción explícita del curso.

2. **Procesar la imagen en el cliente móvil** (TensorFlow Lite + Capacitor).
   ⚠️ Requiere modelo cuantizado, hardware decente; complica la app Ionic.
   ❌ Trasladar trabajo de IA al móvil no es lo que pide el enunciado.

3. **Servicio Python embebido en el mismo container** (Laravel + uvicorn).
   ❌ Acopla los ciclos de vida; cae uno → cae el otro.

## Consecuencias

✅ Separación clara de responsabilidades (Single Responsibility a nivel servicio).
✅ Cada servicio escala independiente.
✅ El servicio ML puede actualizar su modelo sin tocar Laravel.

⚠️ Más complejidad operativa: hay que levantar 2 procesos (gunicorn + php-fpm).
   Mitigado con Docker Compose en producción.

⚠️ Latencia extra del HTTP-call entre servicios. Aceptable para uso humano
   (~1-2s para una foto), pero malo para volúmenes altos. Out of scope.
