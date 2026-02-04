from django.shortcuts import render, redirect, get_object_or_404
from django.http import JsonResponse
from django.views.decorators.http import require_GET
from django.contrib.auth.models import User
from django.contrib.auth import authenticate, login, logout
from django.contrib import messages
from core.models import Categoria, Producto
from django.db.models import Q, F
from django.conf import settings
import os
import json
import random

# Mapeo de términos de categorías del XML a las 5 categorías principales
# Cada categoría agrupa múltiples términos que podrían venir del XML
CATEGORIA_MAP = {
    'consumo': [
        'consumo', 'aceites', 'conservas', 'fideos', 'arroz', 'harina', 'azucar',
        'salsas', 'condimentos', 'especias', 'enlatados', 'legumbres', 'granos',
        'cafe', 'te', 'chocolate', 'leche', 'lacteos', 'yogurt', 'queso', 'mantequilla',
        'huevo', 'pan', 'galletas', 'cereal', 'barras', 'mermelada', 'miel',
        'abarrotes', 'despensa', 'comestibles', 'alimentacion', 'alimentos'
    ],
    'limpieza-y-hogar': [
        'limpieza', 'hogar', 'detergente', 'jabon', 'suavizante', 'cloro', 'lejia',
        'desinfectante', 'limpiador', 'abrasivo', 'escoba', 'trapeador', 'recogedor',
        'plasticos', 'platos', 'vasos', 'cubiertos', 'envases', 'papel',
        'servilletas', 'toallas', 'bolsas', 'basura', 'aluminio', 'film',
        'perfumeria', 'jabones', 'shampoo', 'acondicionador', 'desodorante',
        'higiene', 'personal', 'aseo', 'bauhaus', 'construccion', 'ferreteria'
    ],
    'bebidas': [
        'bebida', 'bebidas', 'gaseosa', 'gaseosas', 'agua', 'jugos', 'jugo',
        'jugo', 'energia', 'energetico', 'energizante', 'licor', 'licores',
        'vino', 'cerveza', 'cervezas', 'refresco', 'refrescos', 'bebida',
        'tonica', 'soda', 'malta', 'isotonico', 'hidratante'
    ],
    'congelados': [
        'congelado', 'congelados', 'helado', 'helados', 'carne', 'carnes',
        'pollo', 'pescado', 'mariscos', 'congelada', 'congelados', 'freeze',
        'vegetales', 'verduras', 'frutas', 'congeladas', 'pizza', 'empanada',
        ' Nuggets', 'pan', 'pastel', 'reposteria', 'dulces', 'postres'
    ],
    'confiteria': [
        'confiteria', 'confite', 'dulces', 'caramelo', 'caramelos', 'chocolate',
        'chocolates', 'chicle', 'chicles', 'golosina', 'golosinas', 'snack',
        'snacks', 'galleta', 'galletas', 'pastel', 'pasteles', 'torta',
        'tortas', 'bizcocho', 'donut', 'donuts', 'cupcake', 'bombon',
        'bombones', 'piramide', 'tableta', 'tableta', 'cacao', 'bomboneria'
    ],
}


# Categorías principales para filtrar
CATEGORIAS_PRINCIPALES = [
    ('consumo', 'Consumo', 'CONSUMO'),
    ('limpieza-y-hogar', 'Limpieza y Hogar', 'LIMPIEZA Y HOGAR'),
    ('bebidas', 'Bebidas', 'BEBIDAS'),
    ('congelados', 'Congelados', 'CONGELADOS'),
    ('confiteria', 'Confitería', 'CONFITERIA'),
]


# ---------------------------
# Página principal
# ---------------------------


def index(request):
    categorias = Categoria.objects.all()
    
    # Mostrar solo 10 productos aleatorios para la página de inicio
    productos = list(Producto.objects.filter(activo=True))
    random.shuffle(productos)
    productos = productos[:10]

    # Filtro por categoría (desde query param ?categoria=...)
    categoria_slug = request.GET.get('categoria')
    if categoria_slug:
        # Usar el diccionario de mapeo para encontrar productos
        categoria_slug_lower = categoria_slug.lower()
        if categoria_slug_lower in CATEGORIA_MAP:
            # Construir consulta Q con icontains para cada término relacionado
            terminos = CATEGORIA_MAP[categoria_slug_lower]
            query = Q()
            for termino in terminos:
                query |= Q(categoria__nombre__icontains=termino)
            productos_filtrados = Producto.objects.filter(activo=True).filter(query)
        else:
            # Fallback: buscar por icontains directo
            productos_filtrados = Producto.objects.filter(
                activo=True,
                categoria__nombre__icontains=categoria_slug.replace('-', ' ')
            )
        productos = list(productos_filtrados)

    # Búsqueda
    q = request.GET.get('q')
    if q:
        productos = list(Producto.objects.filter(
            Q(nombre__icontains=q) | Q(codigo_producto__icontains=q) | Q(categoria__nombre__icontains=q)
        ).filter(activo=True))

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


# ---------------------------
# Carrito de compras
# ---------------------------
def cart_detail(request):
    """
    Vista para mostrar el detalle del carrito de compras.
    Lee los productos del carrito desde localStorage via JavaScript.
    """
    return render(request, 'cart_detail.html')


# ---------------------------
# API de productos (para el carrito)
# ---------------------------
@require_GET
def productos_json(request):
    """
    API para obtener lista de productos en formato JSON.
    Usado por el carrito de compras.
    """
    productos = Producto.objects.filter(activo=True).select_related('categoria')
    placeholder_url = f"{settings.STATIC_URL}img/products/placeholder.svg"
    
    productos_list = []
    for p in productos:
        productos_list.append({
            'id': p.id,
            'codigo_producto': p.codigo_producto,
            'nombre': p.nombre,
            'precio': float(p.precio),
            'categoria': p.categoria.nombre if p.categoria else '',
            'imagen': p.imagen.url if p.imagen else placeholder_url,
            'stock': p.stock,
            'activo': p.activo,
        })
    
    return JsonResponse({'productos': productos_list})


# ---------------------------
# Vista de Categoría
# ---------------------------
def categoria_view(request, slug):
    """
    Vista para mostrar productos de una categoría específica.
    Usa el diccionario CATEGORIA_MAP para agrupar productos por términos relacionados.
    """
    categorias = Categoria.objects.all()
    
    # Usar el diccionario de mapeo para encontrar productos
    slug_lower = slug.lower()
    if slug_lower in CATEGORIA_MAP:
        # Construir consulta Q con icontains para cada término relacionado
        terminos = CATEGORIA_MAP[slug_lower]
        query = Q()
        for termino in terminos:
            query |= Q(categoria__nombre__icontains=termino)
        productos = Producto.objects.filter(activo=True).filter(query)
        # Usar el nombre de la categoría del mapping
        categoria_nombre = dict(CATEGORIAS_PRINCIPALES).get(slug, slug.replace('-', ' ').title())
    else:
        # Fallback: buscar por icontains directo
        productos = Producto.objects.filter(
            activo=True,
            categoria__nombre__icontains=slug.replace('-', ' ')
        )
        categoria_nombre = slug.replace('-', ' ').title()
    
    return render(request, 'index.html', {
        'categorias': categorias,
        'categorias_principales': CATEGORIAS_PRINCIPALES,
        'productos': productos,
        'hero_images': [],
        'categoria_activa': slug,
        'categoria_nombre': categoria_nombre,
        'q': '',
    })
