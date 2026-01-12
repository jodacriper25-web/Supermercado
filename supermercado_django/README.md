# Supermercado (Django)

Proyecto Django inicial generado a partir del código PHP existente en this workspace. Incluye modelos que reflejan `migrations/schema.sql` y una app `core` con admin registration y API básica.

Instrucciones rápidas:

1. Crear y activar un virtualenv (Windows):

```bash
python -m venv .venv
.\.venv\Scripts\activate
```

2. Instalar dependencias:

```bash
pip install -r requirements.txt
```

3. Configurar variables de entorno. Copiar `.env.example` y ajustar si hace falta.

4. Ejecutar migraciones y crear superusuario:

```bash
python manage.py migrate
python manage.py createsuperuser
python manage.py runserver
```

5. Acceder a `http://127.0.0.1:8000/admin/` y a la API en `/api/`.
