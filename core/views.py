from django.shortcuts import render
from .models import Categoria, Producto

def index(request):
    categorias = Categoria.objects.all()
    productos = Producto.objects.all()

    categoria_id = request.GET.get('categoria')
    if categoria_id:
        productos = productos.filter(categoria_id=categoria_id)

    return render(request, 'core/index.html', {
        'categorias': categorias,
        'productos': productos
    })
