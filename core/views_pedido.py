from django.shortcuts import render, redirect
from django.http import JsonResponse
from django.contrib.auth.decorators import login_required
from .models import Pedido

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
        return redirect('index')
    # Si no es POST, redirigir al index
    return redirect('index')


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
