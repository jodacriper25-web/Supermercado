from django.contrib import admin
from .models import Producto, Categoria

@admin.register(Producto)
class ProductoAdmin(admin.ModelAdmin):
    list_display = ('nombre', 'precio', 'precio_oferta', 'categoria')
    list_filter = ('categoria',)
    search_fields = ('nombre',)

admin.site.register(Categoria)