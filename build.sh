#!/usr/bin/env bash
# build.sh - Script para construir la aplicación en Render

set -o errexit  # Detener si hay errores

echo "=== INSTALANDO DEPENDENCIAS ==="
pip install --upgrade pip
pip install -r requirements.txt

echo "=== APLICANDO MIGRACIONES ==="
python manage.py migrate

echo "=== COLECTANDO ARCHIVOS ESTÁTICOS ==="
python manage.py collectstatic --noinput --clear

echo "=== ¡CONSTRUCCIÓN COMPLETADA! ==="