from django.contrib import admin
from django.urls import path
from django.conf import settings
from django.conf.urls.static import static

from core.views import index
from core.views_auth import registro
from core.views_pedido import crear_pedido

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', index, name='index'),
    path('registro/', registro, name='registro'),
    path('pedido/', crear_pedido, name='pedido'),
]

# Servir estáticos y media en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
