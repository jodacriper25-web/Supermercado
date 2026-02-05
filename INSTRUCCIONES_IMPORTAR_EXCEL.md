# Guía de Importación de Productos desde Excel

## 📋 Descripción

Este script de Django permite importar productos desde un archivo Excel (.xlsx o .xls) directamente a la base de datos del Supermercado Yaruquíes.

## ✅ Requisitos Previos

1. **Librerías requeridas** instaladas:
```bash
pip install openpyxl xlrd
```

O instala todas las dependencias del proyecto:
```bash
pip install -r requirements.txt
```

2. **Archivo de Excel** con la siguiente estructura:

| Columna | Campo | Tipo | Ejemplo |
|---------|-------|------|---------|
| A | Código Producto | Texto | 7861038005138 |
| B | Código Referencia | Texto | REF001 |
| C | Nombre/Descripción | Texto | TIPS BAÑO PASTILLA MANZANA 90G |
| D | Categoría | Texto | CONSUMO |
| E | Grupo | Texto | CUIDADO PERSONAL |
| F | Línea | Texto | General |
| G | Stock | Número | 150 |
| H | Precio Normal | Decimal | 1.25 |
| I | Precio Oferta | Decimal | 1.00 (opcional) |
| J | Costo | Decimal | 0.75 |
| K | Imagen | Texto | tips_banio.jpg (opcional) |

**Nota:** La primera fila debe contener los encabezados.

## 🚀 Cómo Usar

### Opción 1: Importar archivo Excel (Primero)

```bash
python manage.py import_excel data/Export.xls
```

**El script automáticamente:**
- ✓ Crea las categorías si no existen
- ✓ Importa los productos con todos sus datos
- ✓ Asigna imágenes si existen en `/media/productos/`
- ✓ Valida los datos antes de guardar
- ✓ Genera un reporte detallado del proceso

### Opción 2: Actualizar productos existentes

Si algunos productos ya existen en la BD y deseas actualizarlos:

```bash
python manage.py import_excel data/Export.xls --actualizar
```

**Comportamiento:**
- Productos nuevos → Se crean
- Productos existentes → Se actualizan con los nuevos datos
- Imágenes → Se asignan si existen

## 📁 Estructura de Archivos

```
Supermercado/
├── core/
│   ├── management/
│   │   └── commands/
│   │       └── import_excel.py    ← Script de importación
├── data/
│   ├── Export.xls                 ← Tu archivo Excel aquí
│   └── Export.xml                 ← XML anterior (ya no se usa)
├── manage.py
└── requirements.txt
```

## 🖼️ Gestión de Imágenes

### Ubicación de Imágenes
Las imágenes debe estén en: `media/productos/`

### Ejemplo:
```
media/
└── productos/
    ├── tips_banio.jpg
    ├── alpina_leche.png
    ├── jolly_chocolate.jpg
    └── ... más imágenes
```

### En el Excel
En la columna K escribe solo el **nombre del archivo**:
```
tips_banio.jpg
alpina_leche.png
jolly_chocolate.jpg
```

El script buscará automáticamente en `media/productos/`

## 📊 Mapeo de Categorías

El script mapea automaticamente categorías del Excel a las 5 categorías principales:

- **CONSUMO** → Abarrotes, alimentos, despensa
- **LIMPIEZA Y HOGAR** → Limpieza, higiene, ferretería
- **BEBIDAS** → Bebidas, jugos, refrescos, licores
- **CONGELADOS** → Carnes, pescados, helados, congelados
- **CONFITERIA** → Dulces, chocolates, snacks, golosinas

## ⚙️ Campos Especiales

### Stock (Columna G)
- Número entero positivo
- Se guarda en `existencia_bodega`

### Precios (Columnas H, I)
- Formato decimal: `1.25` o `1,25`
- Precio Oferta es opcional (dejar vacío si no hay)

### Imagen (Columna K)
- Solo el nombre del archivo: `producto.jpg`
- Formatos: JPG, PNG, JPEG, GIF, WEBP
- Archivo debe existir en `media/productos/`

## 📈 Ejemplos de Ejecución

### Ejemplo 1: Importación inicial
```bash
python manage.py import_excel data/Export.xls
```

**Salida esperada:**
```
Abierto archivo: data/Export.xls
Hoja activa: Productos

✓ Fila 2: Producto creado - TIPS BAÑO PASTILLA MANZANA 90G
✓ Fila 3: Producto creado - ALPINA LECHE DURAZNO 140G
↻ Fila 4: Producto actualizado - ARCOR FRUTILLAS ACIDAS 150G
⊘ Fila 5: Producto ya existe - JOLLY JABÓN MANZANILLA (usa --actualizar)

==================================================
✓ Productos importados: 2
↻ Productos actualizados: 1
✗ Errores: 0
==================================================
```

### Ejemplo 2: Actualizar todos
```bash
python manage.py import_excel data/Export.xls --actualizar
```

## 🐛 Solución de Problemas

### Error: "openpyxl no está instalado"
```bash
pip install openpyxl
```

### Error: "xlrd no está instalado"
```bash
pip install xlrd
```

### Las imágenes no aparecen
1. Verifica que el archivo exista en `media/productos/`
2. Que el nombre en Excel coincida exactamente (incluyendo extensión)
3. Verifica permisos de lectura del archivo
4. Re-ejecuta con `--actualizar` para reasignar imágenes

### Algunos productos no se importaron
Revisa el reporte de errores que muestra el comando para ver qué fila tuvo problema.

## 🎯 Flujo Completo Recomendado

1. **Preparar Excel:**
   - Verifica que tenga la estructura correcta
   - Valida que todos los productos tengan código y nombre

2. **Copiar imágenes:**
   - Coloca los archivos de imagen en `media/productos/`

3. **Ejecutar importación:**
   ```bash
   python manage.py import_excel data/Export.xls
   ```

4. **Revisar resultado:**
   - Verifica en la admin o página web que los productos aparezcan

5. **Actualizar si es necesario:**
   ```bash
   python manage.py import_excel data/Export.xls --actualizar
   ```

## 📝 Notas Importantes

- El script **NO elimina** productos existentes
- El script **SI permite** actualizar datos con `--actualizar`
- Los precios se redondean a 2 decimales automáticamente
- El stock se convierte a número entero
- Las imágenes son opcionales
- El código de producto es único (no puede haber duplicados)

## ✨ Próximos Pasos

Después de importar:
1. Verifica los productos en el admin: `/admin/core/producto/`
2. Prueba que aparecen en el sitio web: `http://127.0.0.1:8000/`
3. Filtra por categoría para verificar el mapeo
4. Comprueba que las imágenes se cargan correctamente

¡Que disfrutes tu Supermercado Yaruquíes! 🛒
