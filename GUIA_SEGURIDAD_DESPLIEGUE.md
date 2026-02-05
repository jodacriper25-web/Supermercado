# 🔒 GUÍA DE MEJORAS DE SEGURIDAD Y DESPLIEGUE

**Fecha:** 4 de Febrero, 2026  
**Proyecto:** Supermercado Yaruquíes  
**Versión:** 2.0 (Con mejoras de seguridad)

---

## 📋 Mejoras Implementadas

### 1️⃣ Configuración de Seguridad (settings.py)

✅ **Instalado:** Support para variables de entorno con `python-dotenv`

**Características:**
- Carga automática de `.env` al iniciar
- Soporte para DEBUG mode condicional
- Headers de seguridad para producción (HTTPS, HSTS, CSP)
- Cookies HttpOnly para protección XSS/CSRF

**Uso en Desarrollo:**
```python
# settings.py carga automáticamente variables de .env
SECRET_KEY = os.getenv('SECRET_KEY', 'dev-key-change-in-production')
DEBUG = os.getenv('DEBUG', 'True') == 'True'
```

**Para Producción:**
```bash
# Crear archivo .env con:
DEBUG=False
SECRET_KEY=your-secure-secret-key
ALLOWED_HOSTS=yourdomain.com,www.yourdomain.com
```

---

### 2️⃣ Sistema de Logging Automático

✅ **Instalado:** Logging rotatorio en 3 categorías

**Archivos Log Generados:**
- `logs/django.log` - Logs generales de la aplicación
- `logs/errors.log` - Errores de Django (ERROR y CRITICAL)
- `logs/security.log` - Eventos de seguridad (login, intentos fallidos)

**Configuración:**
```python
# settings.py
LOGGING = {
    'handlers': {
        'file': {
            'class': 'logging.handlers.RotatingFileHandler',
            'maxBytes': 5 * 1024 * 1024,  # 5 MB
            'backupCount': 5,  # Mantiene 5 archivos
        },
        ...
    }
}
```

**Uso:**
```python
import logging
logger = logging.getLogger('core')
logger.info('Mensaje informativo')
logger.warning('Evento de seguridad')
logger.error('Error en proceso')
```

**Monitorear Logs en Tiempo Real:**
```bash
# Linux/macOS
tail -f logs/django.log

# Windows PowerShell
Get-Content logs/django.log -Tail 10 -Wait
```

---

### 3️⃣ Script de Backup Automático

✅ **Instalado:** Comando Django management para backups

**Ubicación:**
```
core/management/commands/backup_db.py
```

**Uso Manual:**
```bash
# Crear backup
python manage.py backup_db

# Crear backup y mantener solo los últimos 10
python manage.py backup_db --keep 10
```

**Usar con Cron (Linux/macOS):**
```bash
# Editar crontab
crontab -e

# Backup diario a las 2 AM
0 2 * * * cd /path/to/proyecto && python manage.py backup_db

# Backup cada 6 horas
0 */6 * * * cd /path/to/proyecto && python manage.py backup_db
```

**Usar con Task Scheduler (Windows):**
```batch
# Crear archivo backup_scheduler.bat en la carpeta del proyecto
@echo off
cd C:\xampp\htdocs\Supermercado
python manage.py backup_db
```

**Características:**
- Backups automáticos en `backups/db_backup_YYYYMMDD_HHMMSS.sqlite3`
- Rotación automática (mantiene últimos 7 backups)
- Logging de cada backup en `logs/security.log`
- Validación de errores

---

### 4️⃣ Rate Limiting en Login

✅ **Instalado:** Protección contra fuerza bruta personalizada

**Características:**
- Máximo 5 intentos de login fallidos
- Ventana de tiempo: 5 minutos
- Per-IP (basado en dirección IP del cliente)
- Logging de intentos en `logs/security.log`

**Funciones Protegidas:**
- `login_cliente()` - Login para clientes
- `login_admin()` - Login para administradores
- `login_view()` - Login genérico

**Código Implementado:**
```python
# core/security.py
from core.security import rate_limit_login

@rate_limit_login
def login_cliente(request):
    # ... código del login
    log_login_attempt(request, username, success=True)
```

**Mensajes al Usuario:**
```
"Demasiados intentos de login fallidos. Intenta de nuevo en 5 minutos."
```

**Configuración Ajustable** (en `core/security.py`):
```python
LOGIN_ATTEMPT_LIMIT = 5  # Cambiar a 3 para más restrictivo
LOGIN_ATTEMPT_WINDOW = 300  # 5 minutos en segundos
```

---

## 🚀 Guía de Despliegue en Producción

### Paso 1: Preparar variables de entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar .env con valores de producción
nano .env
```

**Contenido de .env:**
```env
DEBUG=False
SECRET_KEY=django-insecure-abc123xyz789...  # Generar con: python -c "from django.core.management.utils import get_random_secret_key; print(get_random_secret_key())"
ALLOWED_HOSTS=yourdomain.com,www.yourdomain.com
DATABASE_URL=postgresql://user:pass@localhost/dbname  # Si usas PostgreSQL
```

### Paso 2: Instalar dependencias

```bash
pip install -r requirements.txt
```

### Paso 3: Ejecutar migraciones

```bash
python manage.py migrate
python manage.py collectstatic --noinput
```

### Paso 4: Crear superuser (admin)

```bash
python manage.py createsuperuser
```

### Paso 5: Agendar backups

```bash
# Usar cron, Task Scheduler, o servicio equivalente
python manage.py backup_db
```

### Paso 6: Configurar servidor web

**Con Gunicorn:**
```bash
pip install gunicorn
gunicorn supermercado.wsgi:application --bind 0.0.0.0:8000
```

**Con systemd (Linux):**
```ini
[Unit]
Description=Supermercado Yaruquíes
After=network.target

[Service]
Type=notify
User=www-data
WorkingDirectory=/path/to/supermercado
Environment="PATH=/path/to/venv/bin"
ExecStart=/path/to/venv/bin/gunicorn \
  --workers 3 \
  --bind unix:/run/gunicorn.sock \
  supermercado.wsgi:application

[Install]
WantedBy=multi-user.target
```

### Paso 7: Configurar Nginx (reverse proxy)

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirigir HTTP a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    # SSL Certificates (usar Let's Encrypt con certbot)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    client_max_body_size 20M;
    
    location /static/ {
        alias /path/to/supermercado/staticfiles/;
    }
    
    location /media/ {
        alias /path/to/supermercado/media/;
    }
    
    location / {
        proxy_pass http://unix:/run/gunicorn.sock;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Paso 8: Certificados SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d yourdomain.com -d www.yourdomain.com
sudo certbot renew --dry-run  # Verificar auto-renovación
```

---

## 📊 Monitoreo en Producción

### Ver Logs

```bash
# Último 50 líneas
tail -n 50 logs/django.log

# Logs de error en tiempo real
tail -f logs/errors.log

# Logs de seguridad
tail -f logs/security.log

# Buscar logins fallidos
grep "FAILED" logs/security.log

# Buscar IP sospechosas
grep "Rate limit exceeded" logs/security.log
```

### Crear Backup

```bash
python manage.py backup_db
ls -lh backups/  # Ver tamaño de backups
```

### Limpiar Logs Antiguos (opcional)

```bash
# Eliminar logs con más de 30 días
find logs/ -name "*.log" -mtime +30 -delete
```

---

## 🔐 Checklist de Seguridad

```
☑ DEBUG = False en producción
☑ SECRET_KEY segura y única
☑ ALLOWED_HOSTS configurado
☑ HTTPS/SSL habilitado
☑ Rate limiting activado
☑ Logs monitoreados
☑ Backups automatizados
☑ Permisos de carpeta correctos (logs: 755, backups: 700)
☑ Base de datos respaldada regularmente
☑ Email de alertas configurado (opcional)
```

---

## 🆘 Troubleshooting

### Problema: "ModuleNotFoundError: No module named 'dotenv'"

**Solución:**
```bash
pip install python-dotenv
# o
pip install -r requirements.txt
```

### Problema: Logs not created

**Solución:**
```bash
# Crear carpeta logs
mkdir -p logs

# Asegurarse de permisos
chmod 755 logs
```

### Problema: "Too many login attempts"

**Solución - Limpiar intentos fallidos:**
```python
# Shell de Django
python manage.py shell
from django.core.cache import cache
cache.clear()
```

### Problema: Backups muy grandes

**Solución:**
```bash
# Reducir backups guardados
python manage.py backup_db --keep 3

# Comprimir backups antiguos
gzip backups/db_backup_*.sqlite3
```

---

## 📞 Soporte

Para preguntas sobre estas mejoras:
1. Revisar `logs/django.log` para detalles de errores
2. Revisar `logs/security.log` para eventos de login
3. Contactar al equipo de desarrollo

---

**Versión:** 2.0  
**Última actualización:** 4 de Febrero, 2026
