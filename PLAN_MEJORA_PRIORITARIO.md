# 🔧 PLAN DE MEJORA PRIORITARIO - SUPERMERCADO YARUQUÍES

**Objetivo:** Mejorar calificación de 4.2/5.0 (84%) a 4.7/5.0 (94%)  
**Tiempo estimado:** 4-5 horas  
**Impacto:** +10 puntos en rúbrica

---

## 🎯 MEJORAS RÁPIDAS (Que harían la Diferencia)

### 1️⃣ CONFIGURACIÓN DE SEGURIDAD EN PRODUCCIÓN [30 min]

**Estado Actual:** DEBUG=True (desarrollo)  
**Impacto:** +5 puntos

**Cambios en `supermercado/settings.py`:**

```python
# Cambiar el DEBUG
DEBUG = False

# Agregar al final del archivo
SECURE_SSL_REDIRECT = True
SESSION_COOKIE_SECURE = True
CSRF_COOKIE_SECURE = True
SECURE_BROWSER_XSS_FILTER = True
X_FRAME_OPTIONS = 'DENY'

# Headers de seguridad
SECURE_CONTENT_SECURITY_POLICY = {
    'default-src': ("'self'",),
    'style-src': ("'self'", "'unsafe-inline'", "cdn.jsdelivr.net"),
    'script-src': ("'self'", "cdn.jsdelivr.net"),
    'img-src': ("'self'", "data:", "blob:"),
}

# Cookies más seguras
SESSION_COOKIE_HTTPONLY = True
CSRF_COOKIE_HTTPONLY = True
```

**URL de referencia:**
https://docs.djangoproject.com/en/4.2/howto/deployment/checklist/

---

### 2️⃣ IMPLEMENTAR LOGGING [45 min]

**Estado Actual:** Sin logging de acciones  
**Impacto:** +4 puntos

**Cambios en `supermercado/settings.py`:**

```python
import os

LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'formatters': {
        'verbose': {
            'format': '{levelname} {asctime} {module} {process:d} {thread:d} {message}',
            'style': '{',
        },
    },
    'handlers': {
        'file': {
            'level': 'INFO',
            'class': 'logging.FileHandler',
            'filename': os.path.join(BASE_DIR, 'logs', 'django.log'),
            'formatter': 'verbose',
        },
        'file_errors': {
            'level': 'ERROR',
            'class': 'logging.FileHandler',
            'filename': os.path.join(BASE_DIR, 'logs', 'errors.log'),
            'formatter': 'verbose',
        },
    },
    'root': {
        'handlers': ['file', 'file_errors'],
        'level': 'INFO',
    },
    'loggers': {
        'django': {
            'handlers': ['file', 'file_errors'],
            'level': 'INFO',
            'propagate': False,
        },
        'core': {
            'handlers': ['file'],
            'level': 'INFO',
            'propagate': False,
        },
    },
}
```

**Crear carpeta logs:**
```bash
mkdir logs
touch logs/.gitkeep
```

**Actualizar `.gitignore`:**
```
logs/
*.log
```

---

### 3️⃣ SCRIPT DE BACKUP AUTOMÁTICO [30 min]

**Crear archivo:** `core/management/commands/backup_db.py`

```python
from django.core.management.base import BaseCommand
from django.conf import settings
import shutil
import os
from datetime import datetime

class Command(BaseCommand):
    help = 'Backup automático de la base de datos'

    def handle(self, *args, **options):
        # Crear carpeta backups si no existe
        backup_dir = os.path.join(settings.BASE_DIR, 'backups')
        if not os.path.exists(backup_dir):
            os.makedirs(backup_dir)

        # Generar nombre con timestamp
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        db_file = os.path.join(settings.BASE_DIR, 'db.sqlite3')
        backup_file = os.path.join(backup_dir, f'db_backup_{timestamp}.sqlite3')

        # Copiar archivo
        shutil.copy2(db_file, backup_file)

        # Mantener solo últimos 7 backups
        backups = sorted([f for f in os.listdir(backup_dir) if f.startswith('db_backup_')])
        if len(backups) > 7:
            for old_backup in backups[:-7]:
                os.remove(os.path.join(backup_dir, old_backup))

        self.stdout.write(self.style.SUCCESS(f'✅ Backup creado: {backup_file}'))
```

**Usar con cron (Linux/macOS) o Task Scheduler (Windows):**

```bash
# Cada día a las 2 AM
0 2 * * * cd /ruta/al/proyecto && python manage.py backup_db
```

---

### 4️⃣ RATE LIMITING EN LOGIN [20 min]

**Instalar django-ratelimit:**
```bash
pip install django-ratelimit
```

**Actualizar `core/views_auth.py`:**

```python
from django_ratelimit.decorators import ratelimit

@ratelimit(key='ip', rate='5/m', method='POST')
def login_view(request):
    # Código existente...
    if request.method == 'POST':
        # ... resto del código
```

**Actualizar `requirements.txt`:**
```
django-ratelimit==4.1.0
```

---

## 📱 MEJORAS DE UX (Que Suman Puntos)

### 5️⃣ AGREGAR BREADCRUMBS [20 min]

**En `core/templates/category.html` (línea 30):**

```html
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mt-3">
  <ol class="breadcrumb bg-dark border-bottom border-danger">
    <li class="breadcrumb-item"><a href="/" class="text-danger">Inicio</a></li>
    <li class="breadcrumb-item"><a href="/" class="text-danger">Categorías</a></li>
    <li class="breadcrumb-item active" aria-current="page">
      {{ categoria_nombre|title }}
    </li>
  </ol>
</nav>
```

**Impacto:** +2 puntos (UX mejorada)

---

### 6️⃣ PAGINATION EN PRODUCTOS [25 min]

**Actualizar `core/views.py` - función categoria_view():**

```python
from django.core.paginator import Paginator

def categoria_view(request, slug):
    # ... código existente hasta productos = ...
    
    # Pagination
    paginator = Paginator(productos, 12)  # 12 por página
    page_number = request.GET.get('page', 1)
    page_obj = paginator.get_page(page_number)
    
    return render(request, 'category.html', {
        'page_obj': page_obj,
        'productos': page_obj.object_list,
        'categoria_nombre': categoria_slug.replace('-', ' '),
        'categorias': CATEGORIAS_PRINCIPALES,
    })
```

**En template:**
```html
<!-- Al final de category.html -->
{% if page_obj.has_other_pages %}
<nav class="mt-4">
  <ul class="pagination justify-content-center">
    {% if page_obj.has_previous %}
      <li class="page-item">
        <a class="page-link" href="?page=1">Primera</a>
      </li>
    {% endif %}
    
    {% for num in page_obj.paginator.page_range %}
      {% if page_obj.number == num %}
        <li class="page-item active"><span class="page-link">{{ num }}</span></li>
      {% else %}
        <li class="page-item"><a class="page-link" href="?page={{ num }}">{{ num }}</a></li>
      {% endif %}
    {% endfor %}
    
    {% if page_obj.has_next %}
      <li class="page-item">
        <a class="page-link" href="?page={{ page_obj.paginator.num_pages }}">Última</a>
      </li>
    {% endif %}
  </ul>
</nav>
{% endif %}
```

**Impacto:** +3 puntos (Performance + UX)

---

### 7️⃣ MEJORAR ACCESIBILIDAD [30 min]

**En `core/templates/base.html`:**

```html
<!-- Actualizar formulario de búsqueda -->
<div class="search-box">
    <label for="search-input" class="visually-hidden">Buscar productos</label>
    <input 
        id="search-input"
        type="text" 
        class="form-control" 
        placeholder="🔍 Buscar..."
        aria-label="Buscar productos en el catálogo"
        aria-describedby="search-help">
</div>

<!-- Actualizar botones -->
<button 
    id="btn-carrito"
    class="btn btn-danger" 
    aria-label="Ver carrito de compras (cantidad: 0)"
    aria-current="page">
    🛒 Carrito
</button>
```

**En `core/templates/category.html`:**

```html
<!-- Agregar alt text a imágenes -->
<img 
    src="{{ producto.imagen.url }}" 
    alt="{{ producto.nombre }} - Imagen del producto"
    class="card-img-top"
    loading="lazy">
```

**Impacto:** +2 puntos (Accesibilidad WCAG AA)

---

## 📊 MEJORAS DE DOCUMENTACIÓN [30 min]

### 8️⃣ AGREGAR DIAGRAMA DE ARQUITECTURA

**Crear `ARQUITECTURA.md`:**

```markdown
# 🏗️ Arquitectura del Proyecto

## Estructura MVC Django

```
┌─────────────────────────────────────────────────────┐
│                  Cliente (Navegador)                 │
│              Bootstrap 5 + JQuery                    │
└─────────────────┬───────────────────────────────────┘
                  │ HTTP/HTTPS
┌─────────────────▼───────────────────────────────────┐
│              Django Application (MVC)               │
├─────────┬──────────────┬──────────────┬─────────────┤
│ Views   │   Templates  │   Static     │   Forms     │
│ (Logic) │   (HTML)     │   (CSS/JS)   │ (Validation)│
└────┬────┴──────────────┴──────────────┴─────────────┘
     │
┌────▼─────────────────────────────────────────────────┐
│            ORM Django Models                         │
├──────────────────────────────────────────────────────┤
│ Categoría → Producto → Pedido → DetallePedido       │
└────┬─────────────────────────────────────────────────┘
     │ SQL Queries
┌────▼─────────────────────────────────────────────────┐
│         SQLite Database (db.sqlite3)                │
├──────────────────────────────────────────────────────┤
│ Tables: auth_user, core_categoria, core_producto... │
└──────────────────────────────────────────────────────┘
```

## Stack Tecnológico

- **Backend:** Django 4.2 (Python web framework)
- **Frontend:** Bootstrap 5 + Custom CSS
- **Database:** SQLite (desarrollo) / PostgreSQL (producción)
- **File Storage:** Media files en `/media/productos/`
- **Auth:** Django built-in authentication

## Flujos Principales

### 1. Compra de Producto
```
Cliente → Ver Producto → Agregar Carrito → Checkout → Pago → Pedido
```

### 2. Importación de Excel
```
Admin carga Excel → Validación → Mapeo categorías → BD → Web visible
```

### 3. Autenticación
```
Registro → Haseo contraseña → Sesión → Login → Acceso a pedidos
```
```

**Impacto:** +2 puntos (Documentación técnica)

---

## ⚡ CHECKLIST DE IMPLEMENTACIÓN

```
MEJORAS CRÍTICAS (Suma +14 puntos)
☐ [ ] 1. Configuración HTTPS + headers seguridad (30 min)
☐ [ ] 2. Logging configurado (45 min)
☐ [ ] 3. Script de backup (30 min)
☐ [ ] 4. Rate limiting en login (20 min)

MEJORAS UX (Suma +7 puntos)
☐ [ ] 5. Breadcrumbs en categorías (20 min)
☐ [ ] 6. Pagination en productos (25 min)
☐ [ ] 7. Aria-labels accesibilidad (30 min)

DOCUMENTACIÓN (Suma +2 puntos)
☐ [ ] 8. Diagrama de arquitectura (30 min)

TOTAL TIEMPO: 4.5 horas
TOTAL IMPACTO: +23 puntos (84% → 94%)
```

---

## 📈 IMPACTO POR CATEGORÍA

| Mejora | RF | RNF | BD | UI | Seg. | Deploy | TOTAL |
|--------|-----|-----|-----|-----|------|--------|-------|
| 1. HTTPS Headers | 0 | 0 | 0 | 0 | +3 | +1 | +4 |
| 2. Logging | 0 | 0 | 0 | 0 | +1 | +2 | +3 |
| 3. Backup | 0 | +1 | 0 | 0 | 0 | +2 | +3 |
| 4. Rate Limiting | 0 | 0 | 0 | 0 | +2 | 0 | +2 |
| 5. Breadcrumbs | 0 | 0 | 0 | +2 | 0 | 0 | +2 |
| 6. Pagination | 0 | +1 | 0 | +2 | 0 | 0 | +3 |
| 7. Aria-labels | 0 | +1 | 0 | +2 | 0 | 0 | +3 |
| 8. Diagrama Arq. | 0 | 0 | 0 | 0 | 0 | +1 | +1 |
| **TOTAL** | **0** | **+3** | **0** | **+6** | **+6** | **+6** | **+21** |

**Nueva puntuación:** 109.1 + 21 = **130.1/140 = 92.9%** ≈ **4.65/5.0**

---

## 🚀 PRÓXIMOS PASOS ESTRATÉGICOS

### Fase 1: Inmediata (Hoy)
1. Implementar mejoras 1-4 (seguridad crítica)
2. Commitar cambios a git
3. Documentar en CHANGELOG

### Fase 2: Corto Plazo (Esta semana)
1. Implementar mejoras 5-7 (UX enhancement)
2. Testing exhaustivo
3. Deploy en Heroku/Render

### Fase 3: Largo Plazo (Opcional)
1. Tests automáticos (pytest)
2. CI/CD pipeline (GitHub Actions)
3. Monitoreo con Sentry
4. Analytics con Google Tag Manager

---

**Versión::** 1.0  
**Fecha:** 4 de Febrero, 2026  
**Autor:** Sistema de Análisis
