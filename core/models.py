from django.db import models
from django.contrib.auth.models import User
from django.core.exceptions import ValidationError

class Categoria(models.Model):
    nombre = models.CharField(max_length=100, unique=True)
    slug = models.SlugField(max_length=120, unique=True, null=True, blank=True)
    imagen = models.ImageField(upload_to='categorias/', null=True, blank=True)

    class Meta:
        verbose_name = "Categoría"
        verbose_name_plural = "Categorías"

    def __str__(self):
        return self.nombre

class Producto(models.Model):
    # Identificación (Base Excel)
    codigo_producto = models.CharField(max_length=50, unique=True, verbose_name="Cód. Producto")
    codigo_referencia = models.CharField(max_length=100, blank=True, null=True, verbose_name="Cód. Referencia")
    nombre = models.CharField(max_length=255, verbose_name="Descripción/Nombre")
    
    # Relación y Clasificación (Base Django)
    categoria = models.ForeignKey(Categoria, on_delete=models.PROTECT, related_name='productos', verbose_name="Categoría Web")
    grupo = models.CharField(max_length=100, verbose_name="Grupo Contable")
    linea_nombre = models.CharField(max_length=100, verbose_name="Nombre Línea")
    
    # Inventario y Stock
    existencia_bodega = models.IntegerField(default=0, verbose_name="Stock Real")
    stock_minimo = models.IntegerField(default=1, verbose_name="Mínimo")
    stock_maximo = models.IntegerField(default=100, verbose_name="Máximo")
    unidad_medida = models.CharField(max_length=50, default="UNIDAD", verbose_name="U. Medida")
    
    # Estructura de Precios y E-commerce
    precio_a = models.DecimalField(max_digits=10, decimal_places=2, verbose_name="Precio Normal (PVP)")
    precio_oferta = models.DecimalField(max_digits=10, decimal_places=2, null=True, blank=True, verbose_name="Precio Descuento")
    costo_promedio = models.DecimalField(max_digits=10, decimal_places=4, verbose_name="Costo Unitario")
    impuesto_porcentaje = models.DecimalField(max_digits=5, decimal_places=2, default=15.00, verbose_name="% IVA")
    
    # Multimedia y Control
    imagen = models.ImageField(upload_to='productos/%Y/%m/', null=True, blank=True, verbose_name="Imagen del Producto")
    activo = models.BooleanField(default=True, verbose_name="Disponible en Web")
    creado = models.DateTimeField(auto_now_add=True)
    ultima_compra = models.DateField(blank=True, null=True, verbose_name="Fecha Última Compra")

    class Meta:
        verbose_name = "Producto"
        verbose_name_plural = "Inventario de Productos"
        ordering = ['-creado']

    def __str__(self):
        return f"{self.codigo_producto} - {self.nombre}"

    @property
    def en_oferta(self):
        return self.precio_oferta is not None and self.precio_oferta < self.precio_a

class Pedido(models.Model):
    ESTADOS = [
        ('pendiente', '💳 Pago Pendiente'),
        ('preparando', '📦 En Preparación'),
        ('enviado', '🚚 En Camino'),
        ('entregado', '✅ Entregado'),
    ]

    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='pedidos')
    direccion = models.CharField(max_length=255)
    barrio = models.CharField(max_length=100)
    referencia = models.TextField(blank=True, help_text="Ej: Casa color verde junto a la tienda.")
    estado = models.CharField(max_length=20, choices=ESTADOS, default='pendiente')
    total = models.DecimalField(max_digits=10, decimal_places=2, default=0.00)
    creado = models.DateTimeField(auto_now_add=True)

    def clean(self):
        # Validación de zona geográfica específica solicitada
        if "yaruquies" not in self.direccion.lower() and "yaruquies" not in self.barrio.lower():
            raise ValidationError("FreshWix: Por el momento solo realizamos entregas en el sector de Yaruquíes.")

class DetallePedido(models.Model):
    pedido = models.ForeignKey(Pedido, on_delete=models.CASCADE, related_name='detalles')
    producto = models.ForeignKey(Producto, on_delete=models.PROTECT)
    cantidad = models.PositiveIntegerField(default=1)
    precio_unitario = models.DecimalField(max_digits=10, decimal_places=2)
    subtotal = models.DecimalField(max_digits=10, decimal_places=2)
    creado = models.DateTimeField(auto_now_add=True)

    def save(self, *args, **kwargs):
        self.subtotal = self.cantidad * self.precio_unitario
        super().save(*args, **kwargs)

    def __str__(self):
        return f"{self.cantidad} x {self.producto.nombre} (Pedido #{self.pedido.id})"

class Wishlist(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    producto = models.ForeignKey(Producto, on_delete=models.CASCADE)
    agregado = models.DateTimeField(auto_now_add=True)

    class Meta:
        unique_together = ('user', 'producto')
        verbose_name = "Lista de Deseos"