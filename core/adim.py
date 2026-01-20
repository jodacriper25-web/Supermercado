from django.contrib import admin
from django.utils.html import format_html
from .models import Producto

@admin.register(Producto)
class ProductoAdmin(admin.ModelAdmin):
    # 1. Configuración de la lista principal
    list_display = (
        'codigo_producto', 
        'nombre', 
        'grupo', 
        'display_precio_a', 
        'display_stock', 
        'impuesto_porcentaje', 
        'activo'
    )
    
    # Permite editar el precio y el stock sin entrar al producto
    list_editable = ('activo',)
    
    # Filtros laterales inteligentes
    list_filter = ('grupo', 'activo', 'linea_nombre', 'unidad_medida')
    
    # Buscador potente
    search_fields = ('codigo_producto', 'nombre', 'codigo_referencia', 'linea_nombre')
    
    # 2. Organización del formulario de edición (Pestañas/Secciones)
    fieldsets = (
        ('Información Básica', {
            'fields': (('codigo_producto', 'codigo_referencia'), 'nombre', ('grupo', 'linea_nombre'), 'activo')
        }),
        ('Control de Precios (PVP)', {
            'fields': (('precio_a', 'precio_b', 'precio_c'), 'impuesto_porcentaje'),
            'classes': ('collapse',), # Se puede contraer
        }),
        ('Inventario y Costos', {
            'fields': (('existencia_bodega', 'unidad_medida'), ('stock_minimo', 'stock_maximo'), 'costo_promedio', 'ultima_compra'),
        }),
    )

    # 3. Funciones visuales personalizadas
    def display_precio_a(self, obj):
        return format_html('<strong style="color: #2ecc71;">${}</strong>', obj.precio_a)
    display_precio_a.short_description = "PVP Actual"

    def display_stock(self, obj):
        # Si el stock es menor al mínimo, se pone en rojo
        color = "#2ecc71" if obj.existencia_bodega > obj.stock_minimo else "#e74c3c"
        return format_html(
            '<span style="background: {}; color: white; padding: 3px 10px; border-radius: 10px; font-weight: bold;">{}</span>',
            color, obj.existencia_bodega
        )
    display_stock.short_description = "Stock Actual"

    # Acción personalizada: Activar/Desactivar productos en masa
    actions = ['marcar_como_inactivo']

    def marcar_como_inactivo(self, request, queryset):
        queryset.update(activo=False)
    marcar_como_inactivo.short_description = "Desactivar productos seleccionados"