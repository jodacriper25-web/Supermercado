from django.contrib import admin
from django.utils.html import format_html
from .models import Producto, Categoria, Pedido, Wishlist

admin.site.site_header = "FreshWix | Sistema de Gestión"
admin.site.site_title = "FreshWix Admin"
admin.site.index_title = "Gestión de Supermercado Premium"

class BaseAdmin(admin.ModelAdmin):
    class Media:
        css = {
            'all': ('admin/css/custom_admin.css',)
        }

@admin.register(Producto)
class ProductoAdmin(BaseAdmin):
    list_display = ('display_thumbnail', 'codigo_producto', 'nombre', 'grupo', 'display_precio_a', 'display_stock', 'activo')
    list_editable = ('activo',)
    list_filter = ('grupo', 'activo', 'linea_nombre', 'unidad_medida', 'categoria')
    search_fields = ('codigo_producto', 'nombre', 'codigo_referencia', 'linea_nombre')

    fieldsets = (
        ('Información Básica', {'fields': (('codigo_producto', 'codigo_referencia'), 'nombre', ('categoria', 'grupo', 'linea_nombre'), 'activo')}),
        ('Control de Precios', {'fields': (('precio_a', 'precio_oferta'), 'impuesto_porcentaje'), 'classes': ('collapse',)}),
        ('Inventario', {'fields': (('existencia_bodega', 'unidad_medida'), ('stock_minimo', 'stock_maximo'), 'costo_promedio', 'ultima_compra')}),
        ('Multimedia', {'fields': ('imagen',)}),
    )

    def display_thumbnail(self, obj):
        if obj.imagen:
            return format_html('<img src="{}" style="width:45px;height:45px;border-radius:8px;object-fit:cover;" />', obj.imagen.url)
        return format_html('<i class="bi bi-box-seam text-muted" style="font-size:24px;"></i>')
    display_thumbnail.short_description = "Img"

    def display_precio_a(self, obj):
        return format_html('<strong style="color:#2ecc71;">${}</strong>', obj.precio_a)
    display_precio_a.short_description = "PVP"

    def display_stock(self, obj):
        color = "#2ecc71" if obj.existencia_bodega > obj.stock_minimo else "#e74c3c"
        return format_html('<span style="background:{}; color:white; padding:4px 12px; border-radius:50px; font-weight:bold;">{}</span>', color, obj.existencia_bodega)
    display_stock.short_description = "Stock"

    actions = ['marcar_como_inactivo']
    def marcar_como_inactivo(self, request, queryset):
        filas = queryset.update(activo=False)
        self.message_user(request, f"{filas} productos ocultados en la web.")
    marcar_como_inactivo.short_description = "Ocultar seleccionados"

@admin.register(Categoria)
class CategoriaAdmin(BaseAdmin):
    list_display = ('nombre',)
    search_fields = ('nombre',)

@admin.register(Pedido)
class PedidoAdmin(BaseAdmin):
    list_display = ('id', 'user', 'estado', 'total', 'creado')
    list_filter = ('estado', 'creado')
    search_fields = ('user__username', 'direccion')

@admin.register(Wishlist)
class WishlistAdmin(BaseAdmin):
    list_display = ('user', 'producto', 'agregado')
