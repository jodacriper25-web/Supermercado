Importar productos desde XML

Este proyecto incluye un comando de Django para importar productos desde el archivo Export.xml.

## Uso

```bash
python manage.py import_products --xml data/Export.xml
```

## Opciones

- `--xml`: (obligatorio) ruta al archivo XML que tiene nodos `tblvista_producto`
- `--images-dir`: carpeta opcional donde buscar imágenes
- `--dry-run`: no realiza cambios, solo muestra cuántos productos se importarían

## Ejemplo con dry-run (para probar)

```bash
python manage.py import_products --xml data/Export.xml --dry-run
```

## Ejemplo con imágenes

```bash
python manage.py import_products --xml data/Export.xml --images-dir ruta/a/imagenes
```

## Notas

- Se crea la categoría automáticamente si no existe (basado en el campo "grupo" del XML)
- Los productos se actualizan si el código ya existe
- El XML usa codificación ISO-8859-1 (caracteres especiales españoles)
- El archivo Export.xml contiene 4183 productos aproximadamente
