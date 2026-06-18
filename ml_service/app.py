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

# COCO class id 19 = "cow". Es la ÚNICA clase aceptada para estimación
# de peso. Cualquier otra (persona, perro, caballo, oveja, etc.) se rechaza
# con error 422 — el sistema NO inventa un peso para no-bovinos
# (requerimiento académico #2, prioridad alta).
CLASS_ID_COW = 19

# Umbral mínimo de confianza para considerar válida la detección de una vaca.
# Subido a 0.50 para evitar falsos positivos. Si YOLO no está al menos 50%
# seguro de que es vaca, rechazamos.
CONFIDENCE_THRESHOLD = 0.50

# Umbral por encima del cual una detección no-bovina se considera "dominante".
# Sirve para diagnosticar al usuario qué animal sí se detectó (ej.
# "detectamos un perro con 89% de confianza, no una vaca").
NON_COW_DOMINANT_THRESHOLD = 0.50

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

    # Convertimos las detecciones a lista de tuplas para analizar.
    todas = list(zip(
        boxes.xyxy.tolist(),
        boxes.conf.tolist(),
        boxes.cls.tolist(),
    ))

    # Filtramos detecciones de "cow" con confianza suficiente.
    cows = [
        (xyxy, float(conf))
        for xyxy, conf, cls in todas
        if int(cls) == CLASS_ID_COW and float(conf) >= CONFIDENCE_THRESHOLD
    ]

    # Si NO hay vaca, intentamos diagnosticar qué SÍ se detectó para dar
    # un mensaje útil al usuario (ej. "se detectó un caballo, no una vaca").
    if not cows:
        otras = [
            (modelo.names[int(cls)], float(conf))
            for _, conf, cls in todas
            if int(cls) != CLASS_ID_COW and float(conf) >= NON_COW_DOMINANT_THRESHOLD
        ]
        otras.sort(key=lambda x: x[1], reverse=True)
        deteccion_dominante = otras[0] if otras else None
        return _respuesta_sin_deteccion(deteccion_dominante)

    # Si hay varias vacas en la foto, nos quedamos con la de mayor área (la más
    # cercana a la cámara — la "principal").
    cows.sort(key=lambda item: _area_bbox(item[0]), reverse=True)
    bbox, confianza = cows[0]

    # Doble-chequeo: el bbox tiene que ocupar al menos 3% de la imagen.
    # Vacas muy pequeñas suelen ser falsos positivos lejanos o de fondo.
    if _area_bbox(bbox) / max(area_imagen, 1.0) < 0.03:
        return jsonify({
            "error": "La vaca detectada está demasiado lejos en la foto.",
            "sugerencia": "Acercate más al animal para una mejor estimación."
        }), 422

    # ---- Estimación de peso por área del bbox + categoría ---- #
    categoria_hint = request.form.get('categoria', 'auto')
    etapa = _clasificar_etapa(bbox, categoria_hint)
    peso = _estimar_peso_desde_bbox(bbox, area_imagen, etapa)
    logger.info(
        "Detectada vaca conf=%.2f etapa=%s (hint=%s) bbox=%s peso_estimado=%.1fkg",
        confianza, etapa, categoria_hint, [round(v, 1) for v in bbox], peso,
    )

    return jsonify({
        "peso_estimado": round(peso, 2),
        "unidad": "kg",
        "confianza": round(confianza, 3),
        "bbox": [round(v, 1) for v in bbox],
        "metodo": "yolov8n+heuristica_bbox_ar",
        "etapa_detectada": etapa,
    }), 200


# --------------------------------------------------------------------------- #
# Helpers
# --------------------------------------------------------------------------- #

def _area_bbox(xyxy):
    """Calcula el área de un bounding box dado como [x1, y1, x2, y2]."""
    x1, y1, x2, y2 = xyxy
    return max(0.0, (x2 - x1)) * max(0.0, (y2 - y1))


def _clasificar_etapa(bbox, categoria_hint: str = 'auto') -> str:
    """
    Determina la etapa de desarrollo del animal.

    Si el usuario indicó la categoría ('cria'/'joven'/'adulto'), se usa
    directamente — elimina la ambigüedad de distancia en la foto.
    Solo cuando es 'auto' se infiere del aspect ratio del bbox.
    """
    if categoria_hint in ('cria_neonato', 'cria_joven', 'cria_mayor', 'joven', 'adulto'):
        return categoria_hint
    # compatibilidad con valor legacy 'cria'
    if categoria_hint == 'cria':
        return 'cria_mayor'

    # Inferencia por aspect ratio (ancho/alto del bbox).
    # Ternero → bbox más cuadrado; adulto → más elongado horizontalmente.
    x1, y1, x2, y2 = bbox
    ancho = max(0.0, x2 - x1)
    alto  = max(0.0, y2 - y1)
    ar = ancho / max(alto, 1.0)

    if ar <= 1.30:
        return 'cria'
    elif ar <= 1.65:
        return 'joven'
    else:
        return 'adulto'


# Curvas de calibración por etapa/edad: (proporcion_frame → peso_kg).
# Los rangos están basados en datos reales de pesaje de razas comunes en CR
# (Hereford, Brahman, Angus, Pardo Suizo, Holstein).
# Dentro de cada rango, la proporción del frame afina el resultado.
_CURVAS_PESO = {
    # Ternero recién nacido (0-1 mes): 28–50 kg
    'cria_neonato': [
        (0.05, 28.0),
        (0.30, 35.0),
        (0.60, 43.0),
        (0.90, 50.0),
    ],
    # Ternero joven (1-3 meses): 55–180 kg
    'cria_joven': [
        (0.05,  55.0),
        (0.25,  85.0),
        (0.55, 130.0),
        (0.90, 180.0),
    ],
    # Ternero mayor (3-6 meses): 110–260 kg
    'cria_mayor': [
        (0.05, 110.0),
        (0.25, 150.0),
        (0.55, 200.0),
        (0.90, 260.0),
    ],
    # Novillo / Vaquilla (6 meses – 2 años): 200–500 kg
    'joven': [
        (0.05, 200.0),
        (0.20, 280.0),
        (0.45, 370.0),
        (0.70, 440.0),
        (0.90, 500.0),
    ],
    # Vaca / Toro adulto (> 2 años): PESO_MIN_KG – PESO_MAX_KG
    'adulto': [
        (0.05, PESO_MIN_KG),
        (0.15, 220.0),
        (0.35, 400.0),
        (0.60, 620.0),
        (0.85, PESO_MAX_KG),
    ],
}


def _estimar_peso_desde_bbox(bbox, area_imagen, etapa: str = 'adulto'):
    """
    Estima el peso interpolando la proporción del frame en la curva de la etapa.

    La etapa ya viene resuelta por _clasificar_etapa() (hint del usuario o
    inferencia por aspect ratio), por lo que esta función solo hace la
    interpolación lineal dentro del rango de pesos correcto.
    """
    proporcion = _area_bbox(bbox) / max(area_imagen, 1.0)
    calibracion = _CURVAS_PESO.get(etapa, _CURVAS_PESO['adulto'])

    if proporcion <= calibracion[0][0]:
        return calibracion[0][1]
    if proporcion >= calibracion[-1][0]:
        return calibracion[-1][1]

    for i in range(len(calibracion) - 1):
        p1, w1 = calibracion[i]
        p2, w2 = calibracion[i + 1]
        if p1 <= proporcion <= p2:
            t = (proporcion - p1) / (p2 - p1)
            return round(w1 + t * (w2 - w1), 2)

    return calibracion[0][1]  # fallback defensivo


def _respuesta_sin_deteccion(deteccion_dominante=None):
    """
    Respuesta uniforme cuando no se detecta ninguna vaca en la imagen.

    Si YOLO detectó OTRO animal/objeto con alta confianza, lo reportamos en el
    mensaje para que el usuario entienda por qué se rechazó la foto:
    "detectamos un caballo (87%) en lugar de una vaca".
    """
    payload = {
        "error": "No se detectó un animal bovino en la imagen.",
        "sugerencia": "Tomá la foto de cuerpo completo y de costado, con buena luz.",
    }
    if deteccion_dominante:
        clase, conf = deteccion_dominante
        payload["detectado_en_su_lugar"] = {
            "clase": clase,
            "confianza": round(conf, 3),
        }
        # Traducción amigable de los nombres COCO al español para el usuario.
        traducciones = {
            "person": "una persona",
            "horse": "un caballo",
            "sheep": "una oveja",
            "dog": "un perro",
            "cat": "un gato",
            "bird": "un ave",
            "bear": "un oso",
            "elephant": "un elefante",
        }
        nombre_es = traducciones.get(clase, f"un/a {clase}")
        payload["error"] = (
            f"No se detectó una vaca: se detectó {nombre_es} "
            f"con {int(conf * 100)}% de confianza."
        )
    return jsonify(payload), 422


# --------------------------------------------------------------------------- #
# Modo desarrollo
# --------------------------------------------------------------------------- #

if __name__ == "__main__":
    # En producción usar gunicorn:
    #   gunicorn -w 2 -b 0.0.0.0:5000 app:app
    app.run(host="127.0.0.1", port=5000, debug=False)
