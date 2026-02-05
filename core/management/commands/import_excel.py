import os
import sys
from decimal import Decimal
from django.core.management.base import BaseCommand
from django.utils.text import slugify
from core.models import Categoria, Producto

try:
    import openpyxl
except ImportError:
    openpyxl = None

try:
    import xlrd
except ImportError:
    xlrd = None


class Command(BaseCommand):
    help = "Importa productos desde un archivo Excel (.xlsx o .xls)"

    def add_arguments(self, parser):
        parser.add_argument(
            'archivo',
            type=str,
            help='Ruta al archivo Excel (Export.xlsx o Export.xls)'
        )
        parser.add_argument(
            '--actualizar',
            action='store_true',
            help='Actualiza productos existentes en lugar de saltarlos'
        )

    def handle(self, *args, **options):
        archivo = options['archivo']
        actualizar = options.get('actualizar', False)

        if not os.path.exists(archivo):
            self.stdout.write(self.style.ERROR(f'Archivo no encontrado: {archivo}'))
            return

        # Determinar tipo de archivo
        es_xlsx = archivo.lower().endswith('.xlsx')
        es_xls = archivo.lower().endswith('.xls')

        if es_xlsx:
            self._importar_xlsx(archivo, actualizar)
        elif es_xls:
            self._importar_xls(archivo, actualizar)
        else:
            self.stdout.write(self.style.ERROR('El archivo debe ser .xlsx o .xls'))

    def _importar_xlsx(self, archivo, actualizar):
        """Importa desde archivo .xlsx usando openpyxl"""
        if not openpyxl:
            self.stdout.write(
                self.style.ERROR(
                    'openpyxl no está instalado. Instálalo con: pip install openpyxl'
                )
            )
            return

        try:
            wb = openpyxl.load_workbook(archivo)
            sheet = wb.active
            
            self.stdout.write(self.style.SUCCESS(f'Abierto archivo: {archivo}'))
            self.stdout.write(f'Hoja activa: {sheet.title}')
            
            # Procesar filas (saltar encabezado)
            fila_inicio = 2  # Asumir que fila 1 es encabezado
            productos_importados = 0
            productos_actualizados = 0
            errores = 0

            for fila_num, fila in enumerate(sheet.iter_rows(min_row=fila_inicio, values_only=False), start=fila_inicio):
                try:
                    # Extraer valores de las celdas
                    codigo_producto = fila[0].value
                    codigo_referencia = fila[1].value if len(fila) > 1 else None
                    nombre = fila[2].value if len(fila) > 2 else None
                    categoria_nombre = fila[3].value if len(fila) > 3 else 'Otros'
                    grupo = fila[4].value if len(fila) > 4 else 'General'
                    linea_nombre = fila[5].value if len(fila) > 5 else 'General'
                    existencia_bodega = fila[6].value if len(fila) > 6 else 0
                    precio_a = fila[7].value if len(fila) > 7 else 0
                    precio_oferta = fila[8].value if len(fila) > 8 else None
                    costo_promedio = fila[9].value if len(fila) > 9 else 0
                    imagen_nombre = fila[10].value if len(fila) > 10 else None

                    # Validación básica
                    if not codigo_producto or not nombre:
                        self.stdout.write(
                            self.style.WARNING(f'Fila {fila_num}: Código o nombre vacío, saltando')
                        )
                        continue

                    # Convertir a tipos correctos
                    try:
                        existencia_bodega = int(existencia_bodega) if existencia_bodega else 0
                        precio_a = Decimal(str(precio_a)) if precio_a else Decimal('0.00')
                        precio_oferta = Decimal(str(precio_oferta)) if precio_oferta else None
                        costo_promedio = Decimal(str(costo_promedio)) if costo_promedio else Decimal('0.00')
                    except (ValueError, TypeError) as e:
                        self.stdout.write(
                            self.style.WARNING(f'Fila {fila_num}: Error en conversión de datos - {str(e)}')
                        )
                        continue

                    # Obtener o crear categoría
                    categoria, _ = Categoria.objects.get_or_create(
                        nombre=categoria_nombre or 'Otros',
                        defaults={'slug': slugify(categoria_nombre or 'otros')}
                    )

                    # Preparar datos de la imagen
                    imagen_path = None
                    if imagen_nombre:
                        # Intentar encontrar la imagen en media/productos/
                        imagen_path = f'productos/{imagen_nombre}'

                    # Obtener o crear producto
                    producto, creado = Producto.objects.get_or_create(
                        codigo_producto=str(codigo_producto).strip(),
                        defaults={
                            'codigo_referencia': codigo_referencia or '',
                            'nombre': nombre or 'Sin nombre',
                            'categoria': categoria,
                            'grupo': grupo or 'General',
                            'linea_nombre': linea_nombre or 'General',
                            'existencia_bodega': existencia_bodega,
                            'precio_a': precio_a,
                            'precio_oferta': precio_oferta,
                            'costo_promedio': costo_promedio,
                            'activo': True,
                        }
                    )

                    if creado:
                        # Producto nuevo - asignar imagen si existe
                        if imagen_path:
                            try:
                                # Verificar si el archivo existe en media
                                media_path = f'c:\\xampp\\htdocs\\Supermercado\\media\\{imagen_path}'
                                if os.path.exists(media_path):
                                    producto.imagen = imagen_path
                                    producto.save()
                            except Exception as img_error:
                                self.stdout.write(
                                    self.style.WARNING(f'Fila {fila_num}: Error asignando imagen - {str(img_error)}')
                                )
                        productos_importados += 1
                        self.stdout.write(
                            self.style.SUCCESS(f'✓ Fila {fila_num}: Producto creado - {nombre}')
                        )
                    elif actualizar:
                        # Actualizar producto existente
                        producto.codigo_referencia = codigo_referencia or producto.codigo_referencia
                        producto.nombre = nombre or producto.nombre
                        producto.categoria = categoria
                        producto.grupo = grupo or producto.grupo
                        producto.linea_nombre = linea_nombre or producto.linea_nombre
                        producto.existencia_bodega = existencia_bodega
                        producto.precio_a = precio_a
                        producto.precio_oferta = precio_oferta or producto.precio_oferta
                        producto.costo_promedio = costo_promedio
                        
                        if imagen_path:
                            try:
                                media_path = f'c:\\xampp\\htdocs\\Supermercado\\media\\{imagen_path}'
                                if os.path.exists(media_path):
                                    producto.imagen = imagen_path
                            except Exception:
                                pass
                        
                        producto.save()
                        productos_actualizados += 1
                        self.stdout.write(
                            self.style.SUCCESS(f'↻ Fila {fila_num}: Producto actualizado - {nombre}')
                        )
                    else:
                        self.stdout.write(
                            self.style.WARNING(f'⊘ Fila {fila_num}: Producto ya existe - {nombre} (usa --actualizar)')
                        )

                except Exception as e:
                    errores += 1
                    self.stdout.write(
                        self.style.ERROR(f'✗ Fila {fila_num}: Error - {str(e)}')
                    )

            # Resumen
            self.stdout.write('\n' + '='*50)
            self.stdout.write(self.style.SUCCESS(f'✓ Productos importados: {productos_importados}'))
            if actualizar:
                self.stdout.write(self.style.SUCCESS(f'↻ Productos actualizados: {productos_actualizados}'))
            self.stdout.write(self.style.ERROR(f'✗ Errores: {errores}'))
            self.stdout.write('='*50)

        except Exception as e:
            self.stdout.write(self.style.ERROR(f'Error abriendo archivo: {str(e)}'))

    def _importar_xls(self, archivo, actualizar):
        """Importa desde archivo .xls usando xlrd"""
        if not xlrd:
            self.stdout.write(
                self.style.ERROR(
                    'xlrd no está instalado. Instálalo con: pip install xlrd'
                )
            )
            return

        try:
            wb = xlrd.open_workbook(archivo)
            sheet = wb.sheet_by_index(0)
            
            self.stdout.write(self.style.SUCCESS(f'Abierto archivo: {archivo}'))
            self.stdout.write(f'Hoja activa: {sheet.name}')
            
            productos_importados = 0
            productos_actualizados = 0
            errores = 0

            for fila_num in range(1, sheet.nrows):  # Saltar encabezado
                try:
                    fila = sheet.row_values(fila_num)
                    
                    codigo_producto = fila[0] if len(fila) > 0 else None
                    codigo_referencia = fila[1] if len(fila) > 1 else None
                    nombre = fila[2] if len(fila) > 2 else None
                    categoria_nombre = fila[3] if len(fila) > 3 else 'Otros'
                    grupo = fila[4] if len(fila) > 4 else 'General'
                    linea_nombre = fila[5] if len(fila) > 5 else 'General'
                    existencia_bodega = fila[6] if len(fila) > 6 else 0
                    precio_a = fila[7] if len(fila) > 7 else 0
                    precio_oferta = fila[8] if len(fila) > 8 else None
                    costo_promedio = fila[9] if len(fila) > 9 else 0
                    imagen_nombre = fila[10] if len(fila) > 10 else None

                    # Validación básica
                    if not codigo_producto or not nombre:
                        self.stdout.write(
                            self.style.WARNING(f'Fila {fila_num + 1}: Código o nombre vacío, saltando')
                        )
                        continue

                    # Convertir a tipos correctos
                    try:
                        existencia_bodega = int(float(existencia_bodega)) if existencia_bodega else 0
                        precio_a = Decimal(str(precio_a)) if precio_a else Decimal('0.00')
                        precio_oferta = Decimal(str(precio_oferta)) if precio_oferta else None
                        costo_promedio = Decimal(str(costo_promedio)) if costo_promedio else Decimal('0.00')
                    except (ValueError, TypeError) as e:
                        self.stdout.write(
                            self.style.WARNING(f'Fila {fila_num + 1}: Error en conversión - {str(e)}')
                        )
                        continue

                    # Obtener o crear categoría
                    categoria, _ = Categoria.objects.get_or_create(
                        nombre=str(categoria_nombre).strip() or 'Otros',
                        defaults={'slug': slugify(str(categoria_nombre).strip() or 'otros')}
                    )

                    # Preparar datos de la imagen
                    imagen_path = None
                    if imagen_nombre:
                        imagen_path = f'productos/{str(imagen_nombre).strip()}'

                    # Obtener o crear producto
                    producto, creado = Producto.objects.get_or_create(
                        codigo_producto=str(codigo_producto).strip(),
                        defaults={
                            'codigo_referencia': str(codigo_referencia or '').strip(),
                            'nombre': str(nombre or 'Sin nombre').strip(),
                            'categoria': categoria,
                            'grupo': str(grupo or 'General').strip(),
                            'linea_nombre': str(linea_nombre or 'General').strip(),
                            'existencia_bodega': existencia_bodega,
                            'precio_a': precio_a,
                            'precio_oferta': precio_oferta,
                            'costo_promedio': costo_promedio,
                            'activo': True,
                        }
                    )

                    if creado:
                        if imagen_path:
                            try:
                                media_path = f'c:\\xampp\\htdocs\\Supermercado\\media\\{imagen_path}'
                                if os.path.exists(media_path):
                                    producto.imagen = imagen_path
                                    producto.save()
                            except Exception:
                                pass
                        productos_importados += 1
                        self.stdout.write(
                            self.style.SUCCESS(f'✓ Fila {fila_num + 1}: Producto creado - {nombre}')
                        )
                    elif actualizar:
                        producto.codigo_referencia = str(codigo_referencia or '').strip() or producto.codigo_referencia
                        producto.nombre = str(nombre or 'Sin nombre').strip() or producto.nombre
                        producto.categoria = categoria
                        producto.grupo = str(grupo or 'General').strip() or producto.grupo
                        producto.linea_nombre = str(linea_nombre or 'General').strip() or producto.linea_nombre
                        producto.existencia_bodega = existencia_bodega
                        producto.precio_a = precio_a
                        producto.precio_oferta = precio_oferta or producto.precio_oferta
                        producto.costo_promedio = costo_promedio
                        
                        if imagen_path:
                            try:
                                media_path = f'c:\\xampp\\htdocs\\Supermercado\\media\\{imagen_path}'
                                if os.path.exists(media_path):
                                    producto.imagen = imagen_path
                            except Exception:
                                pass
                        
                        producto.save()
                        productos_actualizados += 1
                        self.stdout.write(
                            self.style.SUCCESS(f'↻ Fila {fila_num + 1}: Producto actualizado - {nombre}')
                        )
                    else:
                        self.stdout.write(
                            self.style.WARNING(f'⊘ Fila {fila_num + 1}: Producto ya existe - {nombre} (usa --actualizar)')
                        )

                except Exception as e:
                    errores += 1
                    self.stdout.write(
                        self.style.ERROR(f'✗ Fila {fila_num + 1}: Error - {str(e)}')
                    )

            # Resumen
            self.stdout.write('\n' + '='*50)
            self.stdout.write(self.style.SUCCESS(f'✓ Productos importados: {productos_importados}'))
            if actualizar:
                self.stdout.write(self.style.SUCCESS(f'↻ Productos actualizados: {productos_actualizados}'))
            self.stdout.write(self.style.ERROR(f'✗ Errores: {errores}'))
            self.stdout.write('='*50)

        except Exception as e:
            self.stdout.write(self.style.ERROR(f'Error abriendo archivo: {str(e)}'))
