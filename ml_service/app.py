"""
BovWeight CR — Microservicio de estimación de peso bovino.

Este servicio expone una API HTTP mínima que recibe la foto de un animal
y devuelve una estimación de peso. Se invoca desde la app Laravel a través
de `FotoIAWeightStrategy`.

Stack:
  - Flask 3        : framework HTTP minimal
  - Ultralytics YOLOv8 : detector de objetos (modelo `yolov8n.pt` pre-entrenado en COCO,
                          incluye la clase "cow" con id 19)
  - Pillow         : carga/manipulación de imágenes
  - numpy          : matemática del área del bounding box

Diseño deliberadamente simple: el servicio NO tiene BD ni estado entre
peticiones; el modelo se carga una vez al arrancar y queda en memoria.
"""

import io
import logging
import os

import numpy as np
from flask import Flask, jsonify, request
from PIL import Image
from ultralytics import YOLO

# --------------------------------------------------------------------------- #
# Configuración
# --------------------------------------------------------------------------- #

# COCO class id 19 = "cow". Otros bovinos relevantes que YOLO también detecta:
#   17 = "horse", 18 = "sheep", 20 = "elephant".
# Para BovWeight nos quedamos solo con "cow" (vaca/toro).
CLASS_ID_COW = 19

# Umbral mínimo de confianza del detector. Si la confianza está por debajo,
# rechazamos la imagen para evitar dar pesos sin sentido.
CONFIDENCE_THRESHOLD = 0.35

# Heurística de peso a partir del bounding box.
# Asumimos foto típica de campo: vaca de costado, cuerpo completo en el frame.
# El área del bbox (ancho * alto) en píxeles se mapea a kg con un coeficiente
# calibrado para que una vaca media (~450 kg) que ocupa ~40% del frame dé
# alrededor de 450. Ajustar con datos reales reduciría error.
PESO_MIN_KG = 80.0     # ternero recién nacido
PESO_MAX_KG = 950.0    # toro adulto grande

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)
logger = logging.getLogger("ml_service")

# --------------------------------------------------------------------------- #
# Carga del modelo (una sola vez, al importar el módulo)
# --------------------------------------------------------------------------- #

# yolov8n = "nano": el más liviano (~6 MB). Suficiente para detección
# de un animal grande. Si se requiere más precisión, usar yolov8s o yolov8m.
MODEL_PATH = os.environ.get("YOLO_MODEL", "yolov8n.pt")

logger.info("Cargando modelo YOLOv8 desde %s (la primera vez se descarga)...", MODEL_PATH)
modelo = YOLO(MODEL_PATH)
logger.info("Modelo listo. Clases conocidas: %d", len(modelo.names))

# --------------------------------------------------------------------------- #
# App Flask
# --------------------------------------------------------------------------- #

app = Flask(__name__)


@app.get("/salud")
def salud():
    """Healthcheck. Lo usa Laravel para chequear si el ML está vivo antes de mandar imágenes."""
    return jsonify({"estado": "ok", "modelo": MODEL_PATH}), 200


@app.post("/estimar")
def estimar():
    """
    Endpoint principal. Recibe una imagen multipart/form-data en el campo `imagen`
    y devuelve una estimación de peso.
    """
    if "imagen" not in request.files:
        return jsonify({"error": "Falta el archivo 'imagen' en el form-data."}), 400

    archivo = request.files["imagen"]
    if not archivo.filename:
        return jsonify({"error": "El archivo 'imagen' está vacío."}), 400

    # Carga la imagen en memoria con Pillow.
    try:
        imagen = Image.open(io.BytesIO(archivo.read())).convert("RGB")
    except Exception as exc:
        logger.warning("Imagen inválida: %s", exc)
        return jsonify({"error": "No se pudo leer la imagen."}), 400

    ancho_imagen, alto_imagen = imagen.size
    area_imagen = ancho_imagen * alto_imagen

    # ---- Detección con YOLOv8 ---- #
    # results es una lista (una entrada por imagen). Cada entrada trae .boxes
    # con tensores de xyxy, conf y cls.
    resultados = modelo.predict(
        source=np.array(imagen),
        conf=CONFIDENCE_THRESHOLD,
        verbose=False,
    )

    if not resultados:
        return _respuesta_sin_deteccion()

    boxes = resultados[0].boxes
    if boxes is None or len(boxes) == 0:
        return _respuesta_sin_deteccion()

    # Filtramos solo las detecciones que sean "cow" (class id 19).
    cows = [
        (xyxy, float(conf))
        for xyxy, conf, cls in zip(
            boxes.xyxy.tolist(),
            boxes.conf.tolist(),
            boxes.cls.tolist(),
        )
        if int(cls) == CLASS_ID_COW
    ]

    if not cows:
        return _respuesta_sin_deteccion()

    # Si hay varias vacas en la foto, nos quedamos con la de mayor área (la más
    # cercana a la cámara — la "principal").
    cows.sort(key=lambda item: _area_bbox(item[0]), reverse=True)
    bbox, confianza = cows[0]

    # ---- Estimación de peso por área del bbox ---- #
    peso = _estimar_peso_desde_bbox(bbox, area_imagen)

    logger.info(
        "Detectada vaca conf=%.2f bbox=%s peso_estimado=%.1fkg",
        confianza, [round(v, 1) for v in bbox], peso,
    )

    return jsonify({
        "peso_estimado": round(peso, 2),
        "unidad": "kg",
        "confianza": round(confianza, 3),
        "bbox": [round(v, 1) for v in bbox],
        "metodo": "yolov8n+heuristica_bbox",
    }), 200


# --------------------------------------------------------------------------- #
# Helpers
# --------------------------------------------------------------------------- #

def _area_bbox(xyxy):
    """Calcula el área de un bounding box dado como [x1, y1, x2, y2]."""
    x1, y1, x2, y2 = xyxy
    return max(0.0, (x2 - x1)) * max(0.0, (y2 - y1))


def _estimar_peso_desde_bbox(bbox, area_imagen):
    """
    Mapea el área del bbox (proporción del frame ocupada por el animal) a un peso.

    Hipótesis:
      - Una vaca adulta promedio (~450 kg) ocupa ~40% del frame en una foto típica.
      - Una vaca chica (~150 kg) ocupa ~15%.
      - Un toro grande (~800 kg) ocupa ~65%.

    Por simplicidad usamos una interpolación lineal entre esos puntos.
    Una mejora futura sería un modelo de regresión entrenado con datos reales.
    """
    proporcion = _area_bbox(bbox) / max(area_imagen, 1.0)

    # Puntos de calibración (proporción → peso kg). Ordenados ascendentemente.
    calibracion = [
        (0.05, PESO_MIN_KG),
        (0.15, 150.0),
        (0.40, 450.0),
        (0.65, 800.0),
        (0.90, PESO_MAX_KG),
    ]

    # Interpolación lineal por tramos.
    if proporcion <= calibracion[0][0]:
        return PESO_MIN_KG
    if proporcion >= calibracion[-1][0]:
        return PESO_MAX_KG

    for i in range(len(calibracion) - 1):
        p1, w1 = calibracion[i]
        p2, w2 = calibracion[i + 1]
        if p1 <= proporcion <= p2:
            t = (proporcion - p1) / (p2 - p1)
            return w1 + t * (w2 - w1)

    return PESO_MIN_KG  # fallback defensivo


def _respuesta_sin_deteccion():
    """Respuesta uniforme cuando no se detecta ninguna vaca en la imagen."""
    return jsonify({
        "error": "No se detectó un animal bovino en la imagen.",
        "sugerencia": "Tomá la foto de cuerpo completo y de costado, con buena luz."
    }), 422


# --------------------------------------------------------------------------- #
# Modo desarrollo
# --------------------------------------------------------------------------- #

if __name__ == "__main__":
    # En producción usar gunicorn:
    #   gunicorn -w 2 -b 0.0.0.0:5000 app:app
    app.run(host="127.0.0.1", port=5000, debug=False)
