from django.contrib import admin
from django.urls import path
from django.conf import settings
from django.conf.urls.static import static

from core import views
from core.views_auth import registro
from core.views_pedido import crear_pedido, pedidos_json

urlpatterns = [
    # Admin de Django
    path('admin/', admin.site.urls),

    # Página principal
    path('', views.index, name='index'),

    # Autenticación con tus vistas
    path('login/', views.login_view, name='login'),
    path('register/', views.register_view, name='register'),
    path('logout/', views.logout_view, name='logout'),

    # Registro alternativo (si necesitas otro flujo)
    path('registro/', registro, name='registro'),

    # Pedidos
    path('pedido/crear/', crear_pedido, name='pedido'),
    path('pedidos/json/', pedidos_json, name='pedidos_json'),

    # Panel del cliente (requiere login)
    path('admin_cliente/', views.admin_cliente_view, name='admin_cliente'),
]

# Media y static solo en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
