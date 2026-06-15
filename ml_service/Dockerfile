# BovWeight CR — Microservicio ML (Flask + YOLOv8)
# Imagen liviana de Python. Para producción correr con gunicorn.

FROM python:3.11-slim

# Dependencias del sistema necesarias para Pillow y opencv (que viene con ultralytics)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libgl1 libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Instalar dependencias Python primero (mejor cacheo de capas)
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Pre-descargar el modelo YOLOv8 para no demorar la primera petición.
RUN python -c "from ultralytics import YOLO; YOLO('yolov8n.pt')"

COPY app.py .

EXPOSE 5000

CMD ["gunicorn", "-w", "2", "-b", "0.0.0.0:5000", "--timeout", "60", "app:app"]
