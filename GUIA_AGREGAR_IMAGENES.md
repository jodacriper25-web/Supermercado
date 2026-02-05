# 📸 GUÍA: CÓMO AGREGAR IMÁGENES A LOS PRODUCTOS

**Fecha:** 4 de Febrero, 2026  
**Proyecto:** Supermercado Yaruquíes

---

## 🎯 DÓNDE COLOCAR LAS IMÁGENES

Las imágenes deben ir en la siguiente carpeta:

```
c:\xampp\htdocs\Supermercado\media\productos\
```

### 📁 Estructura de Carpetas Actual:

```
Supermercado/
├── media/                          👈 Carpeta de archivos
│   ├── productos/                  👈 **AQUÍ VAN LAS IMÁGENES**
│   │   ├── 2026/
│   │   │   └── 02/                (Subcarpetas por año/mes)
│   │   │       └── (imágenes uploadadas automáticamente)
│   │   └── 📸 (tus imágenes aquí)
│
├── core/
├── supermercado/
└── manage.py
```

---

## 📋 PROCESO PASO A PASO

### Paso 1: Preparar tus imágenes

**Formatos soportados:**
- ✅ `.jpg` o `.jpeg` (JPEG)
- ✅ `.png` (PNG - recomendado para logos)
- ✅ `.gif` (GIF animado)
- ✅ `.webp` (WebP - más comprimido)

**Tamaño recomendado:**
- Ancho: 400-600 px
- Alto: 400-600 px
- Tamaño máximo: 2 MB por imagen
- Formato: Horizontal (landscape) para mejor presentación

### Paso 2: Copiar imágenes a la carpeta

**Opción A: Copiar manualmente (Recomendado)**

1. Abre el Explorador de Windows
2. Navega a: `C:\xampp\htdocs\Supermercado\media\productos\`
3. Copia tus archivos `.jpg` o `.png` aquí
4. Ejemplo de nombres:
   ```
   ✓ leche-alpina.png
   ✓ cafe-nescafe.jpg
   ✓ agua-cristalina.png
   ✓ chocolate-nestle.jpg
   ✓ detergente-tide.png
   ```

**Opción B: Desde línea de comandos (PowerShell)**

```powershell
# Copiar un archivo
Copy-Item -Path "C:\Users\Usuario\Downloads\producto.png" -Destination "c:\xampp\htdocs\Supermercado\media\productos\"

# Copiar múltiples archivos
Copy-Item -Path "C:\Users\Usuario\Downloads\*.png" -Destination "c:\xampp\htdocs\Supermercado\media\productos\"

# Copiar toda una carpeta
Copy-Item -Path "C:\Users\Usuario\Downloads\MisProductos\*.jpg" -Destination "c:\xampp\htdocs\Supermercado\media\productos\" -Recurse
```

### Paso 3: Vincular imágenes a productos en la BD

Hay 2 formas de hacerlo:

#### **Opción A: Por Excel (IMPORTACIÓN - RECOMENDADO)**

1. En tu archivo Excel (`data/Export.xls`), agrega una columna llamada `imagen` con:
   ```
   imagen
   ──────────────────
   leche-alpina.png
   cafe-nescafe.jpg
   agua-cristalina.png
   chocolate-nestle.jpg
   detergente-tide.png
   ```

2. Ejecuta el import:
   ```bash
   python manage.py import_excel data/Export.xls
   ```

3. El script automáticamente:
   - ✅ Busca las imágenes en `media/productos/`
   - ✅ Las vincula al producto
   - ✅ Las guarda en la BD

#### **Opción B: Panel Admin Django (Manual)**

1. Ve a: `http://127.0.0.1:8000/admin/`
2. Login con tu usuario admin
3. Click en "Productos" → Selecciona el producto
4. Scroll down a "Imagen"
5. Click en "Browse" y selecciona el archivo
6. Click "Guardar"

---

## 🔍 VERIFICAR QUE LAS IMÁGENES FUNCIONAN

### 1. Ver en el navegador:

```
http://127.0.0.1:8000/
```

Se deben ver las imágenes en:
- Página principal (25 productos)
- Página de categorías
- Tarjetas de productos

### 2. Verificar ruta de la imagen:

**Ruta correcta en la BD:**
```
productos/leche-alpina.png
```

**Ruta en HTML generada:**
```html
<img src="/media/productos/leche-alpina.png" alt="Leche Alpina">
```

---

## ⚙️ CONFIGURACIÓN DJANGO (YA ESTÁ HECHA)

En `settings.py` ya está configurado:

```python
# Carpeta donde se guardan las imágenes
MEDIA_URL = '/media/'
MEDIA_ROOT = BASE_DIR / 'media'
```

En `urls.py` ya está configurado:

```python
# Django sirve automáticamente las imágenes en desarrollo
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
```

---

## 📊 EJEMPLO COMPLETO

### Estado Actual:
```
✓ Carpeta media/productos/ existe
✓ Configuración Django lista
✓ URLs configuradas
✓ Admin listo
```

### Qué debes hacer:
1. **Coloca imágenes en:** `media/productos/`
2. **Nombres de archivo:** `nombre-producto.jpg` o `.png`
3. **Vincula en DB:** Usa Excel import o Admin panel
4. **Visualiza:** `http://127.0.0.1:8000/`

### Ejemplo de archivo Excel:
```
codigo_producto | nombre              | imagen
────────────────┼────────────────────┼──────────────────
001             | Leche Alpina 1L    | leche-alpina.png
002             | Café Nescafé 500g  | cafe-nescafe.jpg
003             | Agua Cristalina    | agua-cristal.png
```

**Resultado:** 3 productos con imágenes en la BD✅

---

## 🚨 PROBLEMAS COMUNES

### Problema: "Imagen no se ve (404)"

**Solución:**
```
✓ Verifica que el archivo existe en: media/productos/
✓ Verifica el nombre exacto (mayúsculas importan)
✓ Verifica que está en la BD con la ruta correcta
✓ En navegador: Ctrl+F5 (limpiar caché)
```

### Problema: "No se puede upload desde Admin"

**Solución:**
```bash
# Verifica permisos de carpeta
chmod 755 media/
chmod 755 media/productos/

# En Windows (PowerShell como admin):
icacls "c:\xampp\htdocs\Supermercado\media" /grant Users:F /t
```

### Problema: "Las imágenes se ven cortadas/distorsionadas"

**Solución:**
- Redimensiona las imágenes antes: 400x400 px es ideal
- Usa formato PNG para logos, JPG para fotos
- Usa herramienta: ImageMagick, Advanced Batch Converter, o online tool

---

## 💡 CONSEJOS

### Nombres de archivos recomendados:
```
✓ leche-alpina.png       (separar con guiones)
✓ cafe_nescafe.jpg       (o guiones bajos)
✓ agua_cristalina.png
✓ chocolate_nestle.jpg

✗ Evitar espacios y caracteres especiales ✗
✗ "Leche Alpina.png"     (tiene espacio)
✗ "Café & Cia.jpg"       (tiene caracteres especiales)
✗ "Product#123.png"      (tiene símbolo)
```

### Estructura de carpetas recomendada:
```
media/productos/           (para tus imágenes)
media/productos/2026/02/   (automática para uploads)
media/categorias/          (para logos de categorías)
```

---

## ✅ CHECKLIST FINAL

```
☑ Carpeta media/productos/ existe
☑ Imágenes copiadas en formato .png o .jpg
☑ Nombres de archivo válidos (sin espacios)
☑ Excel tiene columna "imagen" con nombres
☑ Ejecutaste: python manage.py import_excel data/Export.xls
☑ O creaste productos con imágenes en Admin
☑ Verificaste en navegador: http://127.0.0.1:8000/
☑ Las imágenes se ven correctamente
```

---

## 🎯 RESUMEN RÁPIDO

| Paso | Acción | Dónde |
|------|--------|-------|
| 1 | Coloca imágenes .png/.jpg | `media/productos/` |
| 2 | Agrega nombre en Excel o Admin | `data/Export.xls` o Admin |
| 3 | Importa: `python manage.py import_excel data/Export.xls` | Terminal |
| 4 | Abre navegador | `http://127.0.0.1:8000/` |
| 5 | ✅ ¡Listo! | Ve los productos con imágenes |

---

**¿Preguntas?**
- ✓ Si las imágenes no se ven: verifica la ruta en la BD
- ✓ Si no puedes colocar archivos: verifica permisos de carpeta
- ✓ Si quieres más productos: importa más filas en Excel

¡Listo! 🚀
