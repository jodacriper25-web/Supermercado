# Integración con Django (Guía práctica)

Aunque el backend principal del proyecto está en PHP (para compatibilidad con XAMPP), Django puede complementar o reemplazar módulos (reportes, análisis, dashboards o microservicios) manteniendo la base de datos MySQL compartida.

Opciones de integración

- Microservicio REST (recomendado): crea una app Django (usando Django REST Framework) que consuma o exponga endpoints para reportes, sincronización de facturas o administración avanzada. Mantén contratos claros y versiona tus endpoints.

- Acceso directo a la BD MySQL (gestión read-only desde Django): configura `DATABASES` en `settings.py` para apuntar a `supermercado_db`. Para mapear tablas existentes sin alterar esquema, crea modelos con `class Meta: managed = False` y los campos que necesites. Ejemplo:

```python
# reports/models.py
from django.db import models

class Product(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=200)
    price = models.DecimalField(max_digits=10, decimal_places=2)

    class Meta:
        db_table = 'products'
        managed = False
```

Esto permite usar ORM y consultas complejas sin que Django intente ejecutar migraciones sobre las tablas ya existentes.

- Módulo de reporting y tareas asíncronas: usa Celery (con Redis/RabbitMQ) para ejecutar reportes pesados y generar PDFs/CSVs, dejando el servicio PHP para el flujo de pedidos en tiempo real.

Seguridad y recomendaciones

- Usa un usuario de base de datos con permisos mínimos (por ejemplo, un usuario de solo lectura para reportes y uno con permisos limitados para tareas que deban insertar registros).
- Si Django necesita escribir en la misma BD, coordina las migraciones y evita que Django y PHP compitan por cambios en el esquema. Usa `managed = False` para tablas administradas por PHP.
- Para comunicación entre servicios, emplea autenticación basada en tokens (API keys o JWT) y TLS si los servicios se exponen fuera de la red local.

Ejemplo rápido: API de reportes con Django REST Framework

- Serializador simple:

```python
# reports/serializers.py
from rest_framework import serializers
from .models import Order

class OrderSerializer(serializers.ModelSerializer):
    class Meta:
        model = Order
        fields = ['id','user_id','total','status','created_at']
```

- ViewSet y rutas:

```python
# reports/views.py
from rest_framework import viewsets
from .models import Order
from .serializers import OrderSerializer

class OrderViewSet(viewsets.ReadOnlyModelViewSet):
    queryset = Order.objects.all()
    serializer_class = OrderSerializer
```

Y registra el route en `urls.py` con `router.register('orders', OrderViewSet)`.

Notas finales

- Para reporting avanzado (gráficos, exportaciones y ETL) Django facilita tareas que en PHP pueden requerir librerías adicionales y planificación de jobs; por eso es útil tener Django como microservicio separado, conectándose por API o directamente a la BD (modo read-only).
- Documenta los contratos entre servicios (endpoints, formatos, autorizaciones) y crea pruebas de integración para validar que las dos piezas (PHP y Django) sigan funcionando tras cambios.

Si quieres, puedo generar un proyecto Django de ejemplo (estructura + modelos para `orders`, `invoices`) en un subdirectorio `integration/django_reports/` con instrucciones y endpoints listos para usar — ¿quieres que lo cree ahora?