import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# 1. Configuración de estáticos
STATIC_URL = 'static/'
STATICFILES_DIRS = [os.path.join(BASE_DIR, 'core/static')]

# Esto le dice a Django dónde buscar archivos estáticos adicionales
STATICFILES_DIRS = [
    os.path.join(BASE_DIR, 'core/static'),
]

# 2. Configuración de Media (para las fotos de productos)
MEDIA_URL = '/media/'
MEDIA_ROOT = os.path.join(BASE_DIR, 'media')
