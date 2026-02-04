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
        productos = productos.filter(categoria__nombre__iexact=categoria_slug.replace('-', ' '))

    # Búsqueda
    q = request.GET.get('q')
    if q:
        productos = productos.filter(
            Q(nombre__icontains=q) | Q(codigo_producto__icontains=q) | Q(categoria__nombre__icontains=q)
        )

    # Detectar imágenes para el carrusel (carpeta: core/static/img/hero)
    hero_images = []
    try:
        hero_dir = settings.BASE_DIR / 'core' / 'static' / 'img' / 'hero'
        if hero_dir.exists():
            files = [f for f in hero_dir.iterdir() if f.suffix.lower() in ('.jpg', '.jpeg', '.png', '.svg')]
            files.sort()
            # leer archivo de intervalos si existe (hero/intervals.json)
            intervals = {}
            try:
                int_file = hero_dir / 'intervals.json'
                if int_file.exists():
                    with open(int_file, 'r', encoding='utf-8') as fh:
                        intervals = json.load(fh)
            except Exception:
                intervals = {}

            for f in files:
                ms = int(float(intervals.get(f.name, 30)) * 1000) if intervals.get(f.name) else 30000
                hero_images.append({'filename': f.name, 'interval': ms})
    except Exception:
        hero_images = [{'filename': 'hero1.svg', 'interval': 30}, {'filename': 'hero2.svg', 'interval': 30}]

    return render(request, 'index.html', {
        'categorias': categorias,
        'categorias_principales': CATEGORIAS_PRINCIPALES,
        'productos': productos,
        'hero_images': hero_images,
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
