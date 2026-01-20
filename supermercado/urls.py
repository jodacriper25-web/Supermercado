from django.contrib import admin
from django.urls import path
from django.conf import settings
from django.conf.urls.static import static
from django.contrib.auth import views as auth_views

from core.views import index
from core.views_auth import registro
from core.views_pedido import crear_pedido

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', index, name='index'),

    # Auth
    path('login/', auth_views.LoginView.as_view(template_name='login.html'), name='login'),
    path('logout/', auth_views.LogoutView.as_view(), name='logout'),

    # App
    path('registro/', registro, name='registro'),
    path('pedido/', crear_pedido, name='pedido'),
]

# Media y static SOLO en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
