from django.contrib import admin
from .models import Categoria, Producto

from .models import Pedido, Wishlist

@admin.register(Pedido)
class PedidoAdmin(admin.ModelAdmin):
    list_display = ('id', 'user', 'estado', 'creado')
    list_filter = ('estado',)

@admin.register(Wishlist)
class WishlistAdmin(admin.ModelAdmin):
    list_display = ('user', 'producto')


@admin.register(Categoria)
class CategoriaAdmin(admin.ModelAdmin):
    list_display = ('id', 'nombre')


@admin.register(Producto)
class ProductoAdmin(admin.ModelAdmin):
    list_display = ('nombre', 'categoria', 'precio', 'precio_oferta', 'stock')
    list_filter = ('categoria',)
    search_fields = ('nombre',)
    ordering = ('-creado',)