import os
import xml.etree.ElementTree as ET
from django.core.management.base import BaseCommand, CommandError
from django.conf import settings
from django.core.files import File
from django.utils.text import slugify
from core.models import Categoria, Producto


# Mapeo de categorías del XML a las 5 categorías principales
CATEGORIA_MAP = {
    # CONSUMO - alimentos básicos, abarrotes, etc.
    'consumo': ['CONSUMO', 'ABARROTES', 'ALIMENTOS', 'GRANOS', 'CONSERVAS', 'ARROZ', 'AZUCAR', 'HARINA', 'FIDEOS', 'ACEITE', 'SAL', 'ESPECIAS'],
    # LIMPIEZA Y HOGAR - productos de limpieza
    'limpieza-y-hogar': ['LIMPIEZA', 'HOGAR', 'DETERGENTE', 'JABON', 'SUAVIZANTE', 'LEJIA', 'CLORO', 'LIMPIA', 'DESINFECTANTE', 'PAPEL', 'SERVILLETA'],
    # BEBIDAS - bebidas
    'bebidas': ['BEBIDAS', 'GASEOSA', 'JUGO', 'AGUA', 'CERVEZA', 'VINO', 'REFRESCO', 'BEBIDA'],
    # CONGELADOS - productos congelados
    'congelados': ['CONGELADOS', 'CONGELADO', 'HELADO', 'HIELO', 'FRITO', 'CONGELADA'],
    # CONFITERIA - dulces y snacks
    'confiteria': ['CONFITERIA', 'DULCES', 'GALLETA', 'CHOCOLATE', 'CARAMELO', 'SNACK', 'PISTACHOS', 'FRUTOS SECOS'],
}


def normalizar_categoria(catname):
    """
    Normaliza el nombre de categoría del XML a una de las 5 categorías principales.
    """
    if not catname:
        return 'Varios'
    
    catname_upper = catname.upper()
    
    for main_cat, keywords in CATEGORIA_MAP.items():
        for keyword in keywords:
            if keyword.upper() in catname_upper:
                return main_cat.title()
    
    # Si no coincide ninguna, devolver la original formateada
    return catname.title()


class Command(BaseCommand):
    help = 'Importa productos desde un archivo XML (formato WinDev export) hacia el modelo Producto.'

    def add_arguments(self, parser):
        parser.add_argument('--xml', required=True, help='Ruta al archivo XML a importar')
        parser.add_argument('--images-dir', help='Ruta opcional donde buscar imágenes (por código o nombre)')
        parser.add_argument('--dry-run', action='store_true', help='No guarda en la base, solo muestra resumen')

    def handle(self, *args, **options):
        xml_path = options['xml']
        images_dir = options.get('images_dir')
        dry = options.get('dry_run')

        if not os.path.exists(xml_path):
            raise CommandError(f"Archivo no encontrado: {xml_path}")

        # Parsear XML con codificación correcta
        with open(xml_path, 'r', encoding='iso-8859-1') as f:
            content = f.read()
        
        # Eliminar declaration de stylesheet que puede causar problemas
        content = content.replace('<?xml-stylesheet type="text/xsl" href="Export.xsl"?>', '')
        
        root = ET.fromstring(content)

        created = 0
        updated = 0
        skipped = 0

        for prod_node in root.findall('.//tblvista_producto'):
            # Extraer todos los campos como diccionario
            text_map = {}
            for child in prod_node:
                # Normalizar el tag a minúsculas para comparación
                tag_lower = child.tag.lower()
                text_map[tag_lower] = (child.text or '').strip()

            # Buscar código - buscar campo que contenga 'c' y 'd' y 'producto' (para Céd._Producto)
            code = None
            for key in text_map.keys():
                if 'c' in key and 'd' in key and 'producto' in key and 'referencia' not in key:
                    code = text_map[key]
                    break
            
            # Buscar descripción - buscar campo que contenga 'descripci'
            name = None
            for key in text_map.keys():
                if 'descripci' in key:
                    name = text_map[key]
                    break
            
            # Buscar grupo/categoría
            catname = text_map.get('grupo', 'Varios')
            
            # Normalizar categoría a las 5 principales
            normalized_catname = normalizar_categoria(catname)
            
            categoria, _ = Categoria.objects.get_or_create(
                nombre=normalized_catname, 
                defaults={'slug': slugify(normalized_catname)}
            )
            # Buscar precio A (precio principal)
            precio = text_map.get('precio_a')
            
            # Buscar costo promedio
            costo = None
            for key in text_map.keys():
                if 'costo' in key and 'promedio' in key:
                    costo = text_map[key]
                    break
            
            # Buscar existencia - buscar campo que contenga 'exist' y 'bod'
            stock = None
            for key in text_map.keys():
                if 'exist' in key and 'bod' in key:
                    stock = text_map[key]
                    break
            
            # Buscar línea
            linea = None
            for key in text_map.keys():
                if 'nombre_l' in key:
                    linea = text_map[key]
                    break

            if not code or not name:
                skipped += 1
                continue

            # Normalizar datos
            code = code.strip()
            name = name.strip()
            catname = catname.strip() if catname else 'Varios'
            
            prod_values = {
                'nombre': name,
                'categoria': categoria,
                'grupo': catname,  # Guardamos la categoría original del XML
                'linea_nombre': linea or '',
                'precio_a': float(precio) if precio else 0,
                'costo_promedio': float(costo) if costo else 0,
                'existencia_bodega': int(float(stock)) if stock else 0,
            }

            # Buscar código de referencia
            for key in text_map.keys():
                if 'referencia' in key:
                    prod_values['codigo_referencia'] = text_map[key]
                    break

            prod, created_flag = Producto.objects.update_or_create(codigo_producto=code, defaults=prod_values)

            if created_flag:
                created += 1
            else:
                updated += 1

            # Buscar imagen si se pasó carpeta
            if images_dir and os.path.isdir(images_dir):
                found = None
                base_names = [code, slugify(name), slugify(name).replace('-', '_')]
                for bn in base_names:
                    for ext in ('.jpg', '.jpeg', '.png', '.svg'):
                        candidate = os.path.join(images_dir, bn + ext)
                        if os.path.exists(candidate):
                            found = candidate
                            break
                    if found:
                        break

                if found and not dry:
                    media_folder = os.path.join(settings.MEDIA_ROOT, 'productos', prod.creado.strftime('%Y'), prod.creado.strftime('%m'))
                    os.makedirs(media_folder, exist_ok=True)
                    dest = os.path.join(media_folder, os.path.basename(found))
                    with open(found, 'rb') as fh_src, open(dest, 'wb') as fh_dst:
                        fh_dst.write(fh_src.read())
                    # asignar al campo imagen (ruta relativa a MEDIA_ROOT)
                    prod.imagen = os.path.join('productos', prod.creado.strftime('%Y'), prod.creado.strftime('%m'), os.path.basename(found))
                    prod.save()

        self.stdout.write(self.style.SUCCESS(f"Import terminado. Creados: {created}, Actualizados: {updated}, Saltados: {skipped}"))
        if dry:
            self.stdout.write(self.style.WARNING('Ejecutado en modo dry-run; no se guardaron cambios.'))
