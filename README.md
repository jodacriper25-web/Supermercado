# Despliegue en PythonAnywhere / Render

## 1. Requisitos
- Python 3.10+
- Django (ver requirements.txt)
- Base de datos SQLite (o PostgreSQL en producción)

## 2. Instalación de dependencias
```
pip install -r requirements.txt
```

## 3. Variables de entorno
- Configura `DEBUG=False` y un `SECRET_KEY` seguro en `supermercado/settings.py`.
- Configura `ALLOWED_HOSTS` con tu dominio o IP.

## 4. Migraciones y archivos estáticos
```
python manage.py migrate
python manage.py collectstatic
```

## 5. Despliegue en PythonAnywhere
- Sube el proyecto.
- Configura el WSGI y la ruta del proyecto.
- Asegúrate de que los archivos estáticos y media estén correctamente configurados.

## 6. Despliegue en Render.com
- Crea un nuevo servicio web (Django).
- Sube el código o conecta el repo.
- Configura los comandos de build y start:
  - Build: `pip install -r requirements.txt && python manage.py migrate && python manage.py collectstatic --noinput`
  - Start: `gunicorn supermercado.wsgi`

## 7. Notas
- Revisa la documentación oficial de cada plataforma para detalles específicos.
- Puedes agregar instrucciones para PostgreSQL si lo usas en producción.
