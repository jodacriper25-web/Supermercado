from django.shortcuts import render, redirect
from django.http import JsonResponse
from django.contrib.auth.decorators import login_required, user_passes_test
from django.contrib import messages
from django.views.decorators.http import require_http_methods
from django.core.management import call_command
from io import StringIO
import xml.etree.ElementTree as ET
from .models import Pedido, Producto


# Vista de importar XML (solo admin)
@user_passes_test(lambda u: u.is_staff)
def admin_import_xml(request):
    """
    Vista oculta para importar productos desde XML.
    URL: /admin-importar-xml/ (no visible en menú)
    """
    if request.method == 'POST':
        # Procesar archivo XML
        xml_file = request.FILES.get('xml_file')
        
        if not xml_file:
            messages.error(request, "Por favor selecciona un archivo XML")
            return render(request, 'admin_import_xml.html')
        
        # Validar que sea un archivo XML
        if not xml_file.name.endswith('.xml'):
            messages.error(request, "Solo se permiten archivos .xml")
            return render(request, 'admin_import_xml.html')
        
        try:
            # Leer y validar el XML
            content = xml_file.read().decode('iso-8859-1')
            
            # Eliminar declaration de stylesheet
            content = content.replace('<?xml-stylesheet type="text/xsl" href="Export.xsl"?>', '')
            
            root = ET.fromstring(content)
            
            # Contar productos
            productos_nodes = root.findall('.//tblvista_producto')
            count = len(productos_nodes)
            
            if count == 0:
                messages.warning(request, "No se encontraron productos en el archivo XML")
                return render(request, 'admin_import_xml.html')
            
            # Mostrar preview sin importar
            if 'preview' in request.POST:
                return render(request, 'admin_import_xml.html', {
                    'preview': True,
                    'count': count,
                    'filename': xml_file.name
                })
            
            # Realizar importación
            if 'import' in request.POST:
                # Usar el comando de gestión
                import io
                from django.core.management.base import CommandError
                
                # Guardar archivo temporalmente
                import os
                from django.conf import settings
                
                temp_path = os.path.join(settings.MEDIA_ROOT, 'temp_import.xml')
                with open(temp_path, 'wb') as f:
                    for chunk in xml_file.chunks():
                        f.write(chunk)
                
                # Ejecutar importación usando el comando existente
                out = StringIO()
                try:
                    call_command('import_products', xml=temp_path, stdout=out)
                    messages.success(request, out.getvalue())
                except CommandError as e:
                    messages.error(request, f"Error en importación: {e}")
                finally:
                    # Limpiar archivo temporal
                    if os.path.exists(temp_path):
                        os.remove(temp_path)
                
                return render(request, 'admin_import_xml.html')
        
        except ET.ParseError as e:
            messages.error(request, f"Error al parsear XML: {e}")
        except Exception as e:
            messages.error(request, f"Error inesperado: {e}")
    
    return render(request, 'admin_import_xml.html')


def checkout_view(request):
    """
    Vista de checkout - verifica autenticación y muestra formulario de pedido.
    Si el usuario no está autenticado, muestra mensaje para registrarse.
    """
    # Verificar si hay productos en el carrito
    cart = request.session.get('cart', {})
    if not cart:
        messages.warning(request, "Tu carrito está vacío")
        return redirect('inicio')
    
    if request.user.is_authenticated:
        # Usuario logueado - mostrar formulario de pedido
        # Obtener productos del carrito para mostrar resumen
        productos_en_carrito = []
        total = 0
        for producto_id, cantidad in cart.items():
            try:
                producto = Producto.objects.get(id=producto_id)
                subtotal = float(producto.precio_a) * cantidad
                productos_en_carrito.append({
                    'producto': producto,
                    'cantidad': cantidad,
                    'precio': producto.precio_a,
                    'subtotal': subtotal
                })
                total += subtotal
            except Producto.DoesNotExist:
                continue
        
        return render(request, 'checkout.html', {
            'productos': productos_en_carrito,
            'total': total
        })
    else:
        # Usuario no autenticado - mostrar mensaje de registro
        messages.info(request, "📝 Para realizar tu pedido, necesitas una cuenta. ¡Regístrate en segundos!")
        return render(request, 'checkout_guest.html')


# Crear un nuevo pedido
@login_required
def crear_pedido(request):
    if request.method == 'POST':
        direccion = request.POST.get('direccion', '')
        barrio = request.POST.get('barrio', '')

        # Validación: solo envíos a "yaruquies"
        if 'yaruquies' not in direccion.lower():
            return render(request, 'core/error_envio.html')

        Pedido.objects.create(
            user=request.user,
            direccion=direccion,
            barrio=barrio
        )
        
        # Limpiar carrito después del pedido
        request.session['cart'] = {}
        
        messages.success(request, "¡Pedido realizado con éxito! Te contactaremos pronto.")
        return redirect('inicio')
    
    # Si no es POST, redirigir al checkout
    return redirect('checkout')


# Retornar pedidos del cliente en JSON
@login_required
def pedidos_json(request):
    if request.user.is_staff:
        return JsonResponse({'error': 'Admin no puede usar esta función'}, status=403)
    
    pedidos = Pedido.objects.filter(user=request.user).order_by('-creado')
    pedidos_data = [
        {
            'id': p.id,
            'fecha': p.creado.strftime("%d %b, %Y"),
            'estado': p.estado,
            'total': float(p.total),
        }
        for p in pedidos
    ]

    return JsonResponse({
        'pedidos': pedidos_data,
        'total_pedidos': pedidos.count(),
        'ultimo_gasto': pedidos.first().total if pedidos.exists() else 0,
    })
