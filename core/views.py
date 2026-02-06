from django.shortcuts import render, redirect, get_object_or_404
from django.urls import reverse
from django.http import JsonResponse, HttpResponse
from django.views.decorators.http import require_GET, require_POST
from django.views.decorators.cache import never_cache
from django.contrib.auth.models import User
from django.contrib.auth import authenticate, login, logout
from django.contrib import messages
from core.models import Categoria, Producto
from core.security import rate_limit_login, check_login_success, log_login_attempt
from django.db.models import Q, F
from django.conf import settings
from urllib.parse import urlencode
import os
import json
import random
import logging

logger = logging.getLogger('core')


# ---------------------------
# Vista personalizada para errores CSRF
# ---------------------------
def csrf_failure_view(request, reason=""):
    """
    Vista personalizada para manejar errores CSRF.
    Redirige a la página de login con un parámetro para recargar el token.
    """
    logger.warning(f'CSRF Error: {reason}')
    # Redirigir a login_cliente con parámetro para forzar recarga
    return redirect(f'{request.path}?csrf_error=true' if request.path != '/' else 'login_cliente?csrf_error=true')

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
    ('consumo', 'Consumo'),
    ('limpieza-y-hogar', 'Limpieza y Hogar'),
    ('bebidas', 'Bebidas'),
    ('congelados', 'Congelados'),
    ('confiteria', 'Confitería'),
]

# Diccionario para búsquedas rápidas
CATEGORIAS_DICT = dict(CATEGORIAS_PRINCIPALES)


# ---------------------------
# Página principal
# ---------------------------


def index(request):
    categorias = Categoria.objects.all()
    
    # Mostrar 10 productos de consumo para San Valentín
    # Construir consulta Q para la categoría consumo
    terminos_consumo = CATEGORIA_MAP['consumo']
    query_consumo = Q()
    for termino in terminos_consumo:
        query_consumo |= Q(categoria__nombre__icontains=termino)
    
    productos = list(Producto.objects.filter(activo=True).filter(query_consumo))
    random.shuffle(productos)
    productos = productos[:10]
    
    # Ofertas especiales para San Valentín: 1 galleta (no manicho con galleta), 1 peluche, 1 chocolate
    oferta_galleta = list(Producto.objects.filter(activo=True, nombre__icontains='galleta').exclude(nombre__icontains='manicho'))
    oferta_peluche = list(Producto.objects.filter(activo=True, nombre__icontains='peluche'))
    oferta_chocolate = list(Producto.objects.filter(activo=True, nombre__icontains='chocolate'))
    
    # Tomar 1 producto de cada tipo si existe
    ofertas = []
    if oferta_galleta:
        ofertas.append(oferta_galleta[0])
    if oferta_peluche:
        ofertas.append(oferta_peluche[0])
    if oferta_chocolate:
        ofertas.append(oferta_chocolate[0])
    
    # Mezclar para que no siempre aparezcan en el mismo orden
    random.shuffle(ofertas)

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
        'ofertas': ofertas,
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
# Página de Acceso (seleccionar Cliente o Administrador)
# ---------------------------
@never_cache
def acceso(request):
    """
    Página inicial donde los usuarios eligen si son Cliente o Administrador
    """
    return render(request, 'acceso.html')


# ---------------------------
# Login de Cliente
# ---------------------------
@never_cache
@rate_limit_login
def login_cliente(request):
    """
    Login para clientes normales
    """
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)
        
        if user is not None:
            # Verificar que no sea administrador
            if not user.is_staff:
                login(request, user)
                check_login_success(request)
                log_login_attempt(request, username, success=True)
                messages.success(request, f"¡Bienvenido {user.username}!")
                return redirect('inicio')
            else:
                log_login_attempt(request, username, success=False)
                messages.error(request, "Esta cuenta es de administrador. Usa el login de admin.")
                return redirect('login_cliente')
        else:
            log_login_attempt(request, username, success=False)
            messages.error(request, "Usuario o contraseña incorrectos")
            return redirect('login_cliente')
    
    return render(request, 'login_cliente.html')


# ---------------------------
# Login de Administrador
# ---------------------------
@never_cache
@rate_limit_login
def login_admin(request):
    """
    Login para administradores del sistema
    """
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)
        
        if user is not None:
            # Verificar que sea administrador
            if user.is_staff:
                login(request, user)
                check_login_success(request)
                log_login_attempt(request, username, success=True)
                messages.success(request, f"¡Bienvenido Admin {user.username}!")
                return redirect('dashboard_admin')
            else:
                log_login_attempt(request, username, success=False)
                messages.error(request, "Esta cuenta no tiene permisos de administrador.")
                return redirect('login_admin')
        else:
            log_login_attempt(request, username, success=False)
            messages.error(request, "Usuario o contraseña incorrectos")
            return redirect('login_admin')
    
    return render(request, 'login_admin.html')


# ---------------------------
# Registro de cliente
# ---------------------------
def register_view(request):
    """
    Página de registro de clientes
    """
    if request.method == "POST":
        username = request.POST.get('username')
        email = request.POST.get('email')
        password = request.POST.get('password')
        password2 = request.POST.get('password2')

        # Validaciones
        if password != password2:
            messages.error(request, "Las contraseñas no coinciden")
            return redirect('registro')

        if User.objects.filter(username=username).exists():
            messages.error(request, "El nombre de usuario ya existe")
            return redirect('registro')

        if User.objects.filter(email=email).exists():
            messages.error(request, "El correo electrónico ya está registrado")
            return redirect('registro')

        # Crear usuario
        user = User.objects.create_user(username=username, email=email, password=password)
        user.is_staff = False
        user.save()

        # Loguear automáticamente
        login(request, user)
        messages.success(request, "¡Registro exitoso! Bienvenido a Supermercado Yaruquíes")
        return redirect('inicio')

    # Si es GET, mostrar formulario de registro
    return render(request, 'registro.html')


# ---------------------------
# Login de usuario
# ---------------------------
@never_cache
def login_view(request):
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)
        if user is not None:
            login(request, user)
            messages.success(request, f"¡Bienvenido {user.username}!")
            return redirect('inicio')
        else:
            messages.error(request, "Usuario o contraseña incorrectos")
            return redirect('inicio')
    
    # Si no es POST, redirigir a la página principal
    return redirect('inicio')


# ---------------------------
# Logout de usuario
# ---------------------------
@never_cache
@require_POST
def logout_view(request):
    """
    Cierra la sesión del usuario.
    Solo acepta POST para prevenir ataques CSRF y problemas de caché del navegador.
    """
    logout(request)
    messages.success(request, "Has cerrado sesión correctamente")
    # Redirigir a acceso usando GET para evitar el mensaje de resubmisión de formulario
    return redirect('acceso')


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
        productos = list(Producto.objects.filter(activo=True).filter(query))
        # Usar el nombre de la categoría del mapping
        categoria_nombre = CATEGORIAS_DICT.get(slug, slug.replace('-', ' ').title())
    else:
        # Fallback: buscar por icontains directo
        productos = list(Producto.objects.filter(
            activo=True,
            categoria__nombre__icontains=slug.replace('-', ' ')
        ))
        categoria_nombre = slug.replace('-', ' ').title()
    
    return render(request, 'category.html', {
        'categorias': categorias,
        'categorias_principales': CATEGORIAS_PRINCIPALES,
        'productos': productos,
        'categoria_activa': slug,
        'categoria_nombre': categoria_nombre,
    })


@require_POST
def buscar(request):
    """
    Vista para manejar búsquedas de productos.
    Recibe POST y redirige a la página de inicio con los resultados.
    """
    busqueda = request.POST.get('busqueda', '').strip()
    if busqueda:
        return redirect(f"{reverse('inicio')}?{urlencode({'q': busqueda})}")
    else:
        return redirect('inicio')


@never_cache
def facturacion_view(request):
    """
    Vista para mostrar el formulario de facturación y datos del carrito.
    """
    from django.contrib.auth.decorators import login_required
    if not request.user.is_authenticated:
        return redirect('login_cliente')
    
    contexto = {
        'usuario': request.user,
        'email': request.user.email if request.user.email else '',
    }
    return render(request, 'factura.html', contexto)


@require_POST
@never_cache
def procesar_factura(request):
    """
    Vista para procesar la factura, generar PDF y enviar a WhatsApp.
    """
    if not request.user.is_authenticated:
        return redirect('login_cliente')
    
    # Obtener datos del cliente
    nombre = request.POST.get('nombre', '').strip()
    apellidos = request.POST.get('apellidos', '').strip()
    email = request.POST.get('email', '').strip()
    telefono = request.POST.get('telefono', '').strip()
    ciudad = request.POST.get('ciudad', '').strip()
    direccion = request.POST.get('direccion', '').strip()
    
    # Validaciones
    if not all([nombre, apellidos, email, telefono, ciudad, direccion]):
        messages.error(request, 'Por favor completa todos los campos.')
        return redirect('facturacion')
    
    try:
        # Generar PDF
        from io import BytesIO
        from reportlab.lib.pagesizes import letter
        from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
        from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
        from reportlab.lib.units import inch
        from reportlab.lib import colors
        from datetime import datetime
        
        # Crear buffer para el PDF
        pdf_buffer = BytesIO()
        doc = SimpleDocTemplate(pdf_buffer, pagesize=letter, topMargin=0.5*inch, bottomMargin=0.5*inch)
        
        # Estilos
        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontSize=24,
            textColor=colors.HexColor('#DC3545'),
            spaceAfter=30,
            alignment=1  # Centro
        )
        
        heading_style = ParagraphStyle(
            'CustomHeading',
            parent=styles['Heading2'],
            fontSize=12,
            textColor=colors.HexColor('#333333'),
            spaceAfter=10,
            textTransform='uppercase'
        )
        
        # Contenido del PDF
        story = []
        
        # Título
        story.append(Paragraph("FACTURA DE COMPRA", title_style))
        story.append(Spacer(1, 0.2*inch))
        
        # Información del supermercado
        info_text = "SUPERMERCADO YARUQUÍES<br/>Yaruquíes, Riobamba - Ecuador<br/>Teléfono: +593 98 361 2109<br/>Email: supermercadoyaruquies@gmail.com"
        story.append(Paragraph(info_text, styles['Normal']))
        story.append(Spacer(1, 0.2*inch))
        
        # Fecha y número de factura
        fecha = datetime.now().strftime("%d/%m/%Y %H:%M")
        story.append(Paragraph(f"<b>Fecha:</b> {fecha}", styles['Normal']))
        story.append(Spacer(1, 0.1*inch))
        
        # Datos del cliente
        story.append(Paragraph("CLIENTE", heading_style))
        cliente_info = f"<b>Nombre:</b> {nombre} {apellidos}<br/><b>Email:</b> {email}<br/><b>Teléfono:</b> {telefono}<br/><b>Ciudad:</b> {ciudad}<br/><b>Dirección:</b> {direccion}"
        story.append(Paragraph(cliente_info, styles['Normal']))
        story.append(Spacer(1, 0.2*inch))
        
        # Tabla de productos (recuperar del carrito del cliente)
        story.append(Paragraph("DETALLE DE COMPRA", heading_style))
        story.append(Spacer(1, 0.1*inch))
        
        # Datos de la tabla
        data_table = [['Producto', 'Categoría', 'Cantidad', 'Precio Unit.', 'Subtotal']]
        
        # Aquí deberíamos obtener los productos del carrito del usuario
        # Por ahora, ponemos un ejemplo
        total = 0
        
        # Tabla de ejemplo (en producción, obtener del sesión/carrito)
        table = Table(data_table, colWidths=[2.5*inch, 1.5*inch, 0.8*inch, 1*inch, 1*inch])
        table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#DC3545')),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 10),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black),
            ('ALIGN', (3, 0), (-1, -1), 'RIGHT'),
        ]))
        story.append(table)
        story.append(Spacer(1, 0.3*inch))
        
        # Total
        story.append(Paragraph("<b>Total a Pagar:</b> Ver detalle en el carrito", styles['Normal']))
        story.append(Spacer(1, 0.4*inch))
        
        # Pie de página
        footer_text = "Gracias por tu compra. Te contactaremos para confirmar tu pedido."
        story.append(Paragraph(footer_text, styles['Normal']))
        
        # Construir PDF
        doc.build(story)
        
        # Preparar respuesta
        pdf_buffer.seek(0)
        
        # Crear respuesta con el PDF
        response = HttpResponse(pdf_buffer, content_type='application/pdf')
        response['Content-Disposition'] = 'attachment; filename="factura_compra.pdf"'
        
        # Guardar en sesión para posterior envío a WhatsApp
        request.session['factura_generada'] = True
        
        return response
    
    except Exception as e:
        logger.error(f'Error generando factura: {str(e)}')
        messages.error(request, 'Error al generar la factura. Intenta nuevamente.')
        return redirect('facturacion')
