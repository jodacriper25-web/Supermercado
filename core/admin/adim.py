from django.contrib import admin
from django.utils.html import format_html
from django.urls import reverse
from django.utils.safestring import mark_safe
from .models import Producto, Categoria, Pedido, Wishlist

# Configuración del panel
admin.site.site_header = "FreshWix | Sistema de Gestión"
admin.site.site_title = "FreshWix Admin"
admin.site.index_title = "Gestión de Supermercado Premium"

# Clase base para aplicar CSS en todos los modelos
class BaseAdmin(admin.ModelAdmin):
    class Media:
        css = {'all': ('admin/css/custom_admin.css',)}
        js = ('admin/js/admin_custom.js',)  # si quieres scripts para front-end dinámico

# --------------------------
# Admin Productos
# --------------------------
@admin.register(Producto)
class ProductoAdmin(BaseAdmin):
    list_display = (
        'display_thumbnail',
        'codigo_producto',
        'nombre',
        'grupo',
        'display_precio_a',
        'display_stock',
        'activo',
        'ver_en_tienda'
    )
    list_editable = ('activo',)
    list_filter = ('grupo', 'activo', 'linea_nombre', 'unidad_medida', 'categoria')
    search_fields = ('codigo_producto', 'nombre', 'codigo_referencia', 'linea_nombre')

    fieldsets = (
        ('Información Básica', {
            'fields': (('codigo_producto', 'codigo_referencia'), 'nombre', ('categoria', 'grupo', 'linea_nombre'), 'activo')
        }),
        ('Control de Precios', {
            'fields': (('precio_a', 'precio_oferta'), 'impuesto_porcentaje'),
            'classes': ('collapse',)
        }),
        ('Inventario y Costos', {
            'fields': (('existencia_bodega', 'unidad_medida'), ('stock_minimo', 'stock_maximo'), 'costo_promedio', 'ultima_compra'),
        }),
        ('Multimedia', {
            'fields': ('imagen',),
        }),
    )

    actions = ['marcar_como_inactivo', 'marcar_como_activo']

    # -------------------
    # Funciones visuales
    # -------------------
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
        return format_html(
            '<span style="background:{}; color:white; padding:4px 12px; border-radius:50px; font-weight:bold;">{}</span>',
            color, obj.existencia_bodega
        )
    display_stock.short_description = "Stock"

    # -------------------
    # Acciones masivas
    # -------------------
    def marcar_como_inactivo(self, request, queryset):
        filas_actualizadas = queryset.update(activo=False)
        self.message_user(request, f"{filas_actualizadas} productos ocultos en la tienda.")
    marcar_como_inactivo.short_description = "Ocultar seleccionados en la tienda"

    def marcar_como_activo(self, request, queryset):
        filas_actualizadas = queryset.update(activo=True)
        self.message_user(request, f"{filas_actualizadas} productos visibles en la tienda.")
    marcar_como_activo.short_description = "Mostrar seleccionados en la tienda"

    # -------------------
    # Link rápido para ver producto en la tienda
    # -------------------
    def ver_en_tienda(self, obj):
        url = reverse('index') + f"#producto-{obj.id}"
        return format_html('<a href="{}" target="_blank">Ver</a>', url)
    ver_en_tienda.short_description = "En tienda"

# --------------------------
# Admin Categorías
# --------------------------
@admin.register(Categoria)
class CategoriaAdmin(BaseAdmin):
    list_display = ('nombre',)
    search_fields = ('nombre',)

# --------------------------
# Admin Pedidos
# --------------------------
@admin.register(Pedido)
class PedidoAdmin(BaseAdmin):
    list_display = ('id', 'user', 'estado', 'total', 'creado')
    list_filter = ('estado', 'creado')
    search_fields = ('user__username', 'direccion')

# --------------------------
# Admin Wishlist
# --------------------------
@admin.register(Wishlist)
class WishlistAdmin(BaseAdmin):
    list_display = ('user', 'producto', 'agregado')
