from django.contrib import admin
from django.urls import path
from django.shortcuts import redirect
from django.conf import settings
from django.conf.urls.static import static

# Importar views
from core import views

from core.views_pedido import crear_pedido, pedidos_json, checkout_view, admin_import_xml
from core.views_dashboard import dashboard_admin

urlpatterns = [
    # Redirigir la raíz al login (acceso)
    path('', lambda request: redirect('acceso', permanent=False), name='root'),

    # Admin de Django
    path('admin/', admin.site.urls),

    # Página principal (productos)
    path('inicio/', views.index, name='inicio'),

    # Búsqueda de productos
    path('buscar/', views.buscar, name='buscar'),

    # Categoría con slug
    path('categoria/<str:slug>/', views.categoria_view, name='categoria'),

    # Dashboard de administración personalizado
    path('dashboard-admin/', dashboard_admin, name='dashboard_admin'),

    # Dashboard personalizado
    path('dashboard/', views.dashboard_view, name='dashboard'),

    # Quiénes Somos
    path('quienes-somos/', views.quienes_somos, name='quienes_somos'),

    # Acceso (seleccionar Cliente o Admin)
    path('acceso/', views.acceso, name='acceso'),

    # Registro y autenticación
    path('register/', views.register_view, name='register'),
    path('registro/', views.register_view, name='registro'),
    path('login/', views.login_view, name='login'),
    path('login-cliente/', views.login_cliente, name='login_cliente'),
    path('login-admin/', views.login_admin, name='login_admin'),
    path('logout/', views.logout_view, name='logout'),

    # Checkout y Pedidos
    path('checkout/', checkout_view, name='checkout'),
    path('pedido/crear/', crear_pedido, name='pedido'),
    path('pedidos/json/', pedidos_json, name='pedidos_json'),

    # Admin oculto - Importación de XML
    path('admin-importar-xml/', admin_import_xml, name='admin_import_xml'),

    # Carrito de compras
    path('carrito/', views.cart_detail, name='cart_detail'),

    # Facturación
    path('facturacion/', views.facturacion_view, name='facturacion'),
    path('facturacion/procesar/', views.procesar_factura, name='procesar_factura'),
    
    # API de productos (para el carrito)
    path('api/productos/', views.productos_json, name='productos_json'),
]

# Servir media y static en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
