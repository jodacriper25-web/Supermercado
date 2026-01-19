from django.shortcuts import render
from .models import Producto, Categoria

def index(request):
    productos = Producto.objects.all()
    categorias = Categoria.objects.all()
    return render(request, 'index.html', {
        'productos': productos,
        'categorias': categorias
    })