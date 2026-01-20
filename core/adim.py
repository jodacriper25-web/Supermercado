# core/admin.py
from django.contrib import admin
from django.utils.html import format_html
from .models import Producto, Categoria, Pedido, Wishlist

# Configuración estética del encabezado del panel
admin.site.site_header = "FreshWix | Sistema de Gestión"
admin.site.site_title = "FreshWix Admin"
admin.site.index_title = "Gestión de Supermercado Premium"

# Clase base para inyectar CSS en todos los modelos que hereden de ella
class BaseAdmin(admin.ModelAdmin):
    class Media:
        css = {
            'all': ('admin/css/custom_admin.css',)
        }

@admin.register(Producto)
class ProductoAdmin(BaseAdmin):
    # 1. Configuración de la lista principal (List View)
    list_display = (
        'display_thumbnail',
        'codigo_producto', 
        'nombre', 
        'grupo', 
        'display_precio_a', 
        'display_stock', 
        'activo'
    )
    
    # Edición rápida desde la lista
    list_editable = ('activo',)
    
    # Filtros laterales inteligentes
    list_filter = ('grupo', 'activo', 'linea_nombre', 'unidad_medida', 'categoria')
    
    # Buscador potente para códigos y descripciones
    search_fields = ('codigo_producto', 'nombre', 'codigo_referencia', 'linea_nombre')
    
    # 2. Organización del formulario de edición (Pestañas/Secciones)
    fieldsets = (
        ('Información Básica', {
            'fields': (('codigo_producto', 'codigo_referencia'), 'nombre', ('categoria', 'grupo', 'linea_nombre'), 'activo')
        }),
        ('Control de Precios (PVP)', {
            'fields': (('precio_a', 'precio_oferta'), 'impuesto_porcentaje'),
            'classes': ('collapse',), # Sección contraíble
        }),
        ('Inventario y Costos', {
            'fields': (('existencia_bodega', 'unidad_medida'), ('stock_minimo', 'stock_maximo'), 'costo_promedio', 'ultima_compra'),
        }),
        ('Multimedia', {
            'fields': ('imagen',),
        }),
    )

    # 3. Funciones visuales personalizadas (Columnas dinámicas)
    def display_thumbnail(self, obj):
        """Muestra una miniatura de la imagen en la lista"""
        if obj.imagen:
            return format_html('<img src="{}" style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover;" />', obj.imagen.url)
        return format_html('<i class="bi bi-box-seam text-muted" style="font-size: 24px;"></i>')
    display_thumbnail.short_description = "Img"

    def display_precio_a(self, obj):
        """Resalta el precio principal en verde"""
        return format_html('<strong style="color: #2ecc71;">${}</strong>', obj.precio_a)
    display_precio_a.short_description = "PVP Actual"

    def display_stock(self, obj):
        """Semáforo de stock: Rojo si es menor al mínimo, Verde si es óptimo"""
        color = "#2ecc71" if obj.existencia_bodega > obj.stock_minimo else "#e74c3c"
        return format_html(
            '<span style="background: {}; color: white; padding: 4px 12px; border-radius: 50px; font-weight: bold; font-size: 0.85rem;">{}</span>',
            color, obj.existencia_bodega
        )
    display_stock.short_description = "Stock Actual"

    # 4. Acciones personalizadas en masa
    actions = ['marcar_como_inactivo']

    def marcar_como_inactivo(self, request, queryset):
        filas_actualizadas = queryset.update(activo=False)
        self.message_user(request, f"{filas_actualizadas} productos han sido desactivados de la web.")
    marcar_como_inactivo.short_description = "Ocultar seleccionados de la tienda"

# Registro de los demás modelos usando la clase BaseAdmin para mantener el CSS
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