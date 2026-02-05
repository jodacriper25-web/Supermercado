from django.contrib.admin.views.decorators import staff_member_required
from django.shortcuts import render
from core.models import Producto, Pedido, User
from django.db.models import Sum, Count, F
from datetime import datetime

@staff_member_required
def dashboard_admin(request):
    # Métricas principales
    total_productos = Producto.objects.count()
    total_usuarios = User.objects.count()
    total_pedidos = Pedido.objects.count()
    total_ventas = Pedido.objects.aggregate(total=Sum('total'))['total'] or 0

    # Ventas por mes (últimos 6 meses)
    ventas_por_mes = (
        Pedido.objects.extra({'mes': "strftime('%%Y-%%m', creado)"})
        .values('mes')
        .annotate(total=Sum('total'))
        .order_by('mes')
    )
    labels = [v['mes'] for v in ventas_por_mes]
    data = [float(v['total']) for v in ventas_por_mes]

    # Productos con stock bajo
    productos_stock_bajo = Producto.objects.filter(existencia_bodega__lte=F('stock_minimo'))

    context = {
        'total_productos': total_productos,
        'total_usuarios': total_usuarios,
        'total_pedidos': total_pedidos,
        'total_ventas': total_ventas,
        'labels': labels,
        'data': data,
        'productos_stock_bajo': productos_stock_bajo,
    }
    return render(request, 'dashboard_admin.html', context)
