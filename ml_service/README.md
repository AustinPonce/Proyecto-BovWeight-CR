# BovWeight CR — Microservicio de Estimación de Peso

Microservicio en Python (Flask + Ultralytics YOLOv8) que recibe una fotografía
de una res bovina y devuelve una estimación de peso aproximado en kilogramos.

## Arquitectura

```
┌────────────────────┐   POST /estimar   ┌─────────────────────┐
│  Laravel BovWeight │ ─── multipart ──▶ │  Flask ml_service   │
│  (FotoIAWeight     │                   │  (YOLOv8 detector)  │
│   Strategy)        │ ◀──── JSON ────── │                     │
└────────────────────┘                   └─────────────────────┘
```

El microservicio NO usa base de datos. Es stateless: recibe imagen, devuelve peso.

## Cómo levantarlo (Windows)

Desde la raíz del proyecto:

```powershell
cd ml_service

# 1) Crear entorno virtual de Python (una sola vez)
python -m venv venv

# 2) Activarlo
venv\Scripts\activate

# 3) Instalar dependencias (la primera vez tarda ~3 min porque baja torch)
pip install -r requirements.txt

# 4) Levantar el servidor (en modo desarrollo)
python app.py
```

El servidor queda escuchando en `http://127.0.0.1:5000`.

La primera petición a `/estimar` descarga el modelo YOLOv8 (`yolov8n.pt`, ~6 MB).
Las siguientes son rápidas porque queda en memoria.

## Endpoints

### `GET /salud`
Healthcheck. Devuelve `{"estado": "ok"}`. Usado por el pipeline CI/CD.

### `POST /estimar`
Recibe una imagen y devuelve la estimación de peso.

**Request:**
- Content-Type: `multipart/form-data`
- Campo `imagen`: archivo JPG/PNG/WEBP

**Response 200 (éxito):**
```json
{
  "peso_estimado": 487.34,
  "unidad": "kg",
  "confianza": 0.91,
  "bbox": [120, 80, 540, 410],
  "metodo": "yolov8n+heuristica_bbox"
}
```

**Response 422 (no se detectó una vaca):**
```json
{
  "error": "No se detectó un animal bovino en la imagen.",
  "sugerencia": "Tomá la foto de cuerpo completo y de costado, con buena luz."
}
```

**Response 400 (sin imagen):**
```json
{"error": "Falta el archivo 'imagen' en el form-data."}
```

## Limitaciones (declaración honesta para la defensa)

1. **Usa un modelo pre-entrenado de propósito general (COCO).**
   YOLOv8 detecta "cow" como una de 80 clases, pero NO está afinado para razas
   bovinas costarricenses (Brahman, Nelore, Pardo Suizo, etc.).

2. **La estimación de peso es heurística, no aprendida.**
   Se calcula a partir del área proporcional del bounding box dentro de la
   imagen, asumiendo una distancia de captura típica. Un modelo de regresión
   entrenado con (foto, peso real medido en báscula) daría precisión mucho mayor.

3. **Para producción se requeriría:**
   - Dataset propio etiquetado (foto + peso báscula + raza + edad).
   - Fine-tuning de YOLO + entrenamiento de un regresor (CNN o gradient boosting).
   - Calibración por raza y por fase de crecimiento.

Esta limitación está documentada también en la UI de la app móvil y web
(aviso "Estimación orientativa, no sustituye báscula oficial").

## Deploy en producción

Para correr fuera de modo desarrollo:

```bash
gunicorn -w 2 -b 0.0.0.0:5000 app:app
```

(El `Dockerfile` simplifica esto, ver archivo adjunto.)
