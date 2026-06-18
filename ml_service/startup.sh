#!/bin/bash
cd /home/site/wwwroot

if [ ! -d "antenv" ]; then
    echo "Instalando dependencias por primera vez..."
    python -m venv antenv
    antenv/bin/pip install --upgrade pip
    antenv/bin/pip install -r requirements.txt
fi

antenv/bin/gunicorn -w 1 -b 0.0.0.0:8000 --timeout 120 app:app
