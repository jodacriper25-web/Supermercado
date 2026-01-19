import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# Configuración de Archivos Estáticos (CSS, JS)
STATIC_URL = 'static/'
STATICFILES_DIRS = [
    BASE_DIR / "core" / "static",
]

# Configuración de Media (Imágenes subidas por el usuario)
MEDIA_URL = '/media/'
MEDIA_ROOT = os.path.join(BASE_DIR, 'media')