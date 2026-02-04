from django.shortcuts import render, redirect
from django.contrib.auth.models import User
from django.contrib.auth import authenticate, login, logout
from django.contrib import messages
from core.models import Categoria, Producto
from django.db.models import Q
from django.conf import settings
import os
import json

# Categorías principales para filtrar
CATEGORIAS_PRINCIPALES = [
    ('CONSUMO', 'Consumo'),
    ('LIMPIEZA Y HOGAR', 'Limpieza y Hogar'),
    ('BEBIDAS', 'Bebidas'),
    ('CONGELADOS', 'Congelados'),
    ('CONFITERIA', 'Confitería'),
]


# ---------------------------
# Página principal
# ---------------------------


def index(request):
    categorias = Categoria.objects.all()
    productos = Producto.objects.filter(activo=True)

    # Filtro por categoría
    categoria_slug = request.GET.get('categoria')
    if categoria_slug:
        productos = productos.filter(categoria__slug=categoria_slug)

    # Búsqueda
    q = request.GET.get('q')
    if q:
        productos = productos.filter(
            Q(nombre__icontains=q) | Q(codigo_producto__icontains=q) | Q(categoria__nombre__icontains=q)
        )

    return render(request, 'index.html', {
        'categorias': categorias,
        'categorias_principales': CATEGORIAS_PRINCIPALES,
        'productos': productos,
        'categoria_activa': categoria_slug,
        'q': q or '',
    })


def quienes_somos(request):
    """
    Página Quiénes Somos - Información del Supermercado Yaruquíes
    """
    return render(request, 'quienes_somos.html')


# ---------------------------
# Registro de cliente
# ---------------------------
def register_view(request):
    if request.method == "POST":
        username = request.POST.get('username')
        email = request.POST.get('email')
        password = request.POST.get('password')
        password2 = request.POST.get('password2')

        # Validaciones
        if password != password2:
            messages.error(request, "Las contraseñas no coinciden")
            return redirect('index')

        if User.objects.filter(username=username).exists():
            messages.error(request, "El nombre de usuario ya existe")
            return redirect('index')

        if User.objects.filter(email=email).exists():
            messages.error(request, "El correo electrónico ya está registrado")
            return redirect('index')

        # Crear usuario
        user = User.objects.create_user(username=username, email=email, password=password)
        user.is_staff = False
        user.save()

        # Loguear automáticamente
        login(request, user)
        messages.success(request, "Registro exitoso. ¡Bienvenido!")
        return redirect('index')

    # Si no es POST, redirigir a la página principal
    return redirect('index')


# ---------------------------
# Login de usuario
# ---------------------------
def login_view(request):
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)
        if user is not None:
            login(request, user)
            messages.success(request, f"¡Bienvenido {user.username}!")
            return redirect('index')
        else:
            messages.error(request, "Usuario o contraseña incorrectos")
            return redirect('index')
    
    # Si no es POST, redirigir a la página principal
    return redirect('index')


# ---------------------------
# Logout de usuario
# ---------------------------
def logout_view(request):
    logout(request)
    messages.success(request, "Has cerrado sesión correctamente")
    return redirect('index')
