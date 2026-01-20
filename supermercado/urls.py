from django.contrib import admin
from django.urls import path
from django.conf import settings
from django.conf.urls.static import static

# Importar views
from core import views
from core.views_pedido import crear_pedido, pedidos_json

urlpatterns = [
    # Admin de Django
    path('admin/', admin.site.urls),

    # Página principal
    path('', views.index, name='index'),

    # Registro y autenticación
    path('register/', views.register_view, name='register'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),

    # Pedidos
    path('pedido/crear/', crear_pedido, name='pedido'),
    path('pedidos/json/', pedidos_json, name='pedidos_json'),
]

# Servir media y static en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
