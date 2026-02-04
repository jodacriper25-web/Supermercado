"""
Script para generar imágenes placeholder para productos.
Genera un SVG simple con el código y nombre del producto.
"""
import os
from django.core.management.base import BaseCommand
from core.models import Producto, Categoria
from django.conf import settings


def generar_svg_producto(producto, categoria_color):
    """Genera un SVG para el producto."""
    codigo = producto.codigo_producto[:15]  # Limitar código
    nombre = producto.nombre[:30] + '...' if len(producto.nombre) > 30 else producto.nombre
    
    svg_content = f'''<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <rect width="400" height="400" fill="#1a1a1a"/>
  <rect x="20" y="20" width="360" height="360" rx="20" fill="{categoria_color}"/>
  <text x="200" y="150" font-family="Arial, sans-serif" font-size="24" fill="white" text-anchor="middle" font-weight="bold">{codigo}</text>
  <text x="200" y="200" font-family="Arial, sans-serif" font-size="18" fill="white" text-anchor="middle">{nombre}</text>
  <text x="200" y="250" font-family="Arial, sans-serif" font-size="16" fill="white" text-anchor="middle">{producto.categoria.nombre}</text>
  <text x="200" y="350" font-family="Arial, sans-serif" font-size="14" fill="#cccccc" text-anchor="middle">Supermercado Yaruquíes</text>
</svg>'''
    
    return svg_content


class Command(BaseCommand):
    help = 'Genera imágenes placeholder para productos sin imagen'

    def add_arguments(self, parser):
        parser.add_argument(
            '--carpeta',
            default='productos',
            help='Carpeta donde guardar las imágenes'
        )
        parser.add_argument(
            '--dry-run',
            action='store_true',
            help='No guarda, solo muestra cuántos se generarían'
        )

    def handle(self, *args, **options):
        carpeta = options['carpeta']
        dry_run = options['dry_run']
        
        # Colores por categoría
        colores_categoria = {
            'CONSUMO': '#E74C3C',       # Rojo
            'LIMPIEZA Y HOGAR': '#3498DB',  # Azul
            'BEBIDAS': '#2ECC71',        # Verde
            'CONGELADOS': '#9B59B6',     # Morado
            'CONFITERIA': '#F39C12',      # Naranja
        }
        
        # Carpeta destino
        media_root = settings.MEDIA_ROOT
        destino_base = os.path.join(media_root, carpeta)
        
        if not dry_run:
            os.makedirs(destino_base, exist_ok=True)
        
        # Contadores
        generados = 0
        saltados = 0
        
        productos_sin_imagen = Producto.objects.filter(imagen='').order_by('categoria__nombre')
        total = productos_sin_imagen.count()
        
        self.stdout.write(f'Productos sin imagen: {total}')
        
        for producto in productos_sin_imagen:
            # Determinar color de la categoría
            cat_nombre = producto.categoria.nombre.upper() if producto.categoria else 'CONSUMO'
            color = colores_categoria.get(cat_nombre, '#7F8C8D')
            
            # Generar nombre de archivo
            año = producto.creado.year if producto.creado else 2025
            mes = producto.creado.month if producto.creado else 1
            nombre_archivo = f'{producto.codigo_producto}.svg'
            
            carpeta_categoria = os.path.join(destino_base, str(año), f'{mes:02d}')
            
            if dry_run:
                self.stdout.write(f'  [DRY-RUN] {nombre_archivo} -> {carpeta_categoria}')
            else:
                os.makedirs(carpeta_categoria, exist_ok=True)
                
                # Generar y guardar SVG
                svg_content = generar_svg_producto(producto, color)
                ruta_completa = os.path.join(carpeta_categoria, nombre_archivo)
                
                with open(ruta_completa, 'w', encoding='utf-8') as f:
                    f.write(svg_content)
                
                # Actualizar producto
                ruta_db = os.path.join(carpeta, str(año), f'{mes:02d}', nombre_archivo)
                producto.imagen.name = ruta_db
                producto.save(update_fields=['imagen'])
                
                generados += 1
                
                if generados % 100 == 0:
                    self.stdout.write(f'Generados: {generados}/{total}')
            
            saltados += 1
        
        if dry_run:
            self.stdout.write(self.style.WARNING(f'[{dry_run}] Se generarían {saltados} imágenes'))
        else:
            self.stdout.write(f'OK Generados: {generados} placeholders')
            self.stdout.write(f'  Carpeta: {destino_base}')
