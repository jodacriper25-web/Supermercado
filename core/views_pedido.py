from django.shortcuts import render, redirect
from .models import Pedido

def crear_pedido(request):
    if request.method == 'POST':
        direccion = request.POST['direccion']
        barrio = request.POST['barrio']

        if 'yaruquies' not in direccion.lower():
            return render(request, 'core/error_envio.html')

        Pedido.objects.create(
            user=request.user,
            direccion=direccion,
            barrio=barrio
        )
        return redirect('index')
