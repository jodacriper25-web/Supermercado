from django.test import TestCase
from django.contrib.auth.models import User
from core.models import Categoria, Producto, Pedido, DetallePedido

class CategoriaModelTest(TestCase):
    def test_crear_categoria(self):
        cat = Categoria.objects.create(nombre="PRUEBA-CAT")
        self.assertEqual(str(cat), "PRUEBA-CAT")

class ProductoModelTest(TestCase):
    def setUp(self):
        self.cat = Categoria.objects.create(nombre="TEST-CAT")
    def test_crear_producto(self):
        prod = Producto.objects.create(
            codigo_producto="P001",
            nombre="Producto Test",
            categoria=self.cat,
            grupo="Test",
            linea_nombre="Linea",
            existencia_bodega=10,
            stock_minimo=2,
            stock_maximo=20,
            unidad_medida="UNIDAD",
            precio_a=5.0,
            costo_promedio=3.0,
            impuesto_porcentaje=12.0
        )
        self.assertEqual(str(prod), "P001 - Producto Test")

class PedidoModelTest(TestCase):
    def setUp(self):
        self.user = User.objects.create_user(username="testuser", password="1234")
        self.cat = Categoria.objects.create(nombre="PEDIDO-CAT")
        self.prod = Producto.objects.create(
            codigo_producto="P002",
            nombre="Producto Pedido",
            categoria=self.cat,
            grupo="Test",
            linea_nombre="Linea",
            existencia_bodega=5,
            stock_minimo=1,
            stock_maximo=10,
            unidad_medida="UNIDAD",
            precio_a=10.0,
            costo_promedio=7.0,
            impuesto_porcentaje=12.0
        )
    def test_crear_pedido_y_detalle(self):
        pedido = Pedido.objects.create(user=self.user, direccion="Dir", barrio="Barrio", total=10)
        detalle = DetallePedido.objects.create(pedido=pedido, producto=self.prod, cantidad=2, precio_unitario=10, subtotal=20)
        self.assertEqual(pedido.detalles.count(), 1)
        self.assertEqual(detalle.subtotal, 20)
