from django.contrib import admin
from django.urls import path
from django.conf import settings
from django.conf.urls.static import static

# Importar views
from core import views

from core.views_pedido import crear_pedido, pedidos_json, checkout_view, admin_import_xml
from core.views_dashboard import dashboard_admin

urlpatterns = [
    # Admin de Django
    path('admin/', admin.site.urls),

    # Página principal
    path('', views.index, name='index'),

    # Dashboard de administración personalizado
    path('dashboard-admin/', dashboard_admin, name='dashboard_admin'),

    # Quiénes Somos
    path('quienes-somos/', views.quienes_somos, name='quienes_somos'),

    # Registro y autenticación
    path('register/', views.register_view, name='register'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),

    # Checkout y Pedidos
    path('checkout/', checkout_view, name='checkout'),
    path('pedido/crear/', crear_pedido, name='pedido'),
    path('pedidos/json/', pedidos_json, name='pedidos_json'),

    # Admin oculto - Importación de XML
    path('admin-importar-xml/', admin_import_xml, name='admin_import_xml'),
]

# Servir media y static en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
