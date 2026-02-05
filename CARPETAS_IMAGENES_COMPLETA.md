# 🗂️ ESTRUCTURA DE CARPETAS - IMÁGENES DE PRODUCTOS

## 📍 UBICACIÓN EXACTA

```
C:\xampp\htdocs\Supermercado\media\productos\
                          ↑      ↑
                        Proyecto Carpeta donde poner imágenes
```

---

## 🏗️ ESTRUCTURA COMPLETA DEL PROYECTO

```
C:\xampp\htdocs\Supermercado\
│
├── media\                          ← Carpeta con archivos (fotos, etc)
│   │
│   ├── productos\                  ← 👈 **AQUÍ SI PONEN LAS IMÁGENES**
│   │   │
│   │   ├── 📸 leche-alpina.png     ← Tus imágenes .png
│   │   ├── 📸 cafe-nescafe.jpg     ← Tus imágenes .jpg
│   │   ├── 📸 agua-cristalina.png  ← Tus imágenes aquí
│   │   ├── 📸 chocolate-nestle.jpg
│   │   │
│   │   └── 2026\                   (esta carpeta se crea automáticamente)
│   │       └── 02\                 (para uploads del admin)
│   │           ├── image1_auto.png
│   │           └── image2_auto.png
│   │
│   └── categorias\                 (opcional, para logos de categorías)
│
├── core\
│   ├── management\
│   │   └── commands\
│   │       └── backup_db.py
│   │
│   ├── static\
│   │   ├── css\
│   │   ├── js\
│   │   └── img\
│   │
│   ├── templates\
│   │   ├── index.html
│   │   ├── category.html
│   │   └── ... (más templates)
│   │
│   ├── migrations\
│   ├── models.py
│   ├── views.py
│   ├── security.py          ← Nueva: Rate limiting
│   └── ... (más archivos)
│
├── supermercado\
│   ├── settings.py          ← Modificado: Security + Logging
│   ├── urls.py
│   └── wsgi.py
│
├── logs\                     ← Nueva: Logs de aplicación
│   ├── django.log
│   ├── errors.log
│   └── security.log
│
├── backups\                  ← Auto-creada: Backups de BD
│   └── db_backup_*.sqlite3
│
├── data\
│   └── Export.xls           ← Tu archivo Excel para importar
│
├── manage.py
├── requirements.txt         ← Modificado: +2 dependencias
├── db.sqlite3              ← Base de datos
├── .env.example            ← Nuevo: Configuración de ejemplo
├── .gitignore              ← Modificado: Control de versión
│
├── GUIA_AGREGAR_IMAGENES.md    ← **LEELO ESTE**
├── GUIA_SEGURIDAD_DESPLIEGUE.md
├── CAMBIOS_REALIZADOS_4FEB.md
└── ... (más documentación)
```

---

## 📸 PASO A PASO: COPIAR IMÁGENES

### Opción 1: Explorador de Windows (Más Fácil)

**1. Abre tu carpeta de descargas:**
```
C:\Users\Usuario\Downloads\
```

**2. Encuentra una imagen:**
```
MisProductos/
├── leche-alpina.png
├── cafe-nescafe.jpg
└── agua-cristalina.png
```

**3. Cópiala (Ctrl+C):**
```
Haz clic derecho en leche-alpina.png → Copiar
```

**4. Ve a la carpeta de destino:**
```
Barra de direcciones del Explorador:
C:\xampp\htdocs\Supermercado\media\productos\

(Presiona Enter)
```

**5. Pega (Ctrl+V):**
```
Haz clic derecho en la carpeta vacía → Pegar

Resultado:
✅ leche-alpina.png está en media/productos/
```

**6. Repite con más imágenes:**
```
✅ cafe-nescafe.jpg
✅ agua-cristalina.png
✅ chocolate-nestle.jpg
✅ detergente-tide.png
... (cuantas quieras)
```

---

### Opción 2: PowerShell (Línea de Comandos)

**Copiar un archivo:**
```powershell
Copy-Item -Path "C:\Users\Usuario\Downloads\leche-alpina.png" `
          -Destination "c:\xampp\htdocs\Supermercado\media\productos\"
```

**Copiar múltiples archivos:**
```powershell
Copy-Item -Path "C:\Users\Usuario\Downloads\*.png" `
          -Destination "c:\xampp\htdocs\Supermercado\media\productos\"
```

**Copiar toda una carpeta:**
```powershell
Copy-Item -Path "C:\Users\Usuario\MisProductos\*" `
          -Destination "c:\xampp\htdocs\Supermercado\media\productos\" `
          -Recurse
```

---

## ✅ VERIFICAR QUE FUNCIONA

### 1. Abre la carpeta en Explorador:

```
C:\xampp\htdocs\Supermercado\media\productos\
```

**Deberías ver:**
```
✓ leche-alpina.png
✓ cafe-nescafe.jpg
✓ agua-cristalina.png
✓ chocolate-nestle.jpg
✓ detergente-tide.png
```

### 2. Agrega las imágenes a tu Excel:

**Archivo:** `data/Export.xls`

```
codigo_producto | nombre              | imagen
────────────────┼────────────────────┼──────────────────
001             | Leche Alpina 1L    | leche-alpina.png      ← Nombre del archivo
002             | Café Nescafé 500g  | cafe-nescafe.jpg
003             | Agua Cristalina    | agua-cristalina.png
004             | Chocolate Nestlé   | chocolate-nestle.jpg
005             | Detergente Tide    | detergente-tide.png
```

### 3. Importa desde línea de comandos:

```bash
cd c:\xampp\htdocs\Supermercado
python manage.py import_excel data/Export.xls
```

**Salida esperada:**
```
✓ Conectando a Excel...
✓ Leyendo 5 productos...
✓ Vinculando imágenes...
  - leche-alpina.png ✓
  - cafe-nescafe.jpg ✓
  - agua-cristalina.png ✓
✓ 5 productos importados correctamente
```

### 4. Abre en navegador:

```
http://127.0.0.1:8000/
```

**Deberías ver:**
```
✅ 25 productos con imágenes
✅ Fotos visibles en cada tarjeta
✅ Sin mensajes de error
```

---

## 🎨 ESPECIFICACIONES DE IMÁGENES

### Formatos Soportados:
| Formato | Extensión | Uso | Tamaño Máx |
|---------|-----------|-----|-----------|
| JPEG | .jpg, .jpeg | Fotos de productos | 2 MB |
| PNG | .png | Logos, transparencia | 2 MB |
| GIF | .gif | Animaciones | 2 MB |
| WebP | .webp | Más comprimido | 2 MB |

### Tamaño Recomendado:
```
Ancho:  400 - 600 px
Alto:   400 - 600 px
Relación: Cuadrado (1:1) o más ancho que alto
Calidad: 72-96 DPI (web)
```

### Nombres de Archivos:
```
✅ Válidos:
   leche-alpina.png
   cafe_nescafe.jpg
   agua-cristalina-250ml.png
   detergente_tide_1kg.jpg

❌ Inválidos:
   Leche Alpina.png        (tiene espacios)
   Café & Cia.jpg          (caracteres especiales)
   Product#123.png         (símbolo especial)
   Imagen (1).jpg          (paréntesis)
```

---

## 🔄 FLUJO COMPLETO: IMAGEN → VISTA

```
1. Copias imagen a:
   C:\xampp\htdocs\Supermercado\media\productos\

2. Agregas en Excel:
   data/Export.xls
   (columna: imagen = "leche-alpina.png")

3. Importas:
   python manage.py import_excel data/Export.xls

4. Django guarda en BD:
   Producto.imagen = "productos/leche-alpina.png"

5. Template HTML renderiza:
   <img src="/media/productos/leche-alpina.png">

6. Navegador descarga:
   http://127.0.0.1:8000/media/productos/leche-alpina.png

7. ¡Se ve en la página! ✅
```

---

## 📊 EJEMPLO PRÁCTICO COMPLETO

### Tu carpeta local con imágenes:
```
C:\Users\Usuario\MisProductos\
├── leche-alpina.png       (320x320 px, 45 KB)
├── cafe-nescafe.jpg       (400x400 px, 52 KB)
├── agua-cristalina.png    (400x400 px, 38 KB)
└── chocolate-nestle.jpg   (350x350 px, 61 KB)
```

### Copias a:
```
C:\xampp\htdocs\Supermercado\media\productos\
├── leche-alpina.png       ✓
├── cafe-nescafe.jpg       ✓
├── agua-cristalina.png    ✓
└── chocolate-nestle.jpg   ✓
```

### Agregas en Excel (data/Export.xls):
```
codigo | nombre           | categoria | imagen
──────────────────────────────────────────────
001    | Leche Alpina     | CONSUMO   | leche-alpina.png
002    | Café Nescafé     | CONSUMO   | cafe-nescafe.jpg
003    | Agua Cristalina  | BEBIDAS   | agua-cristalina.png
004    | Chocolate Nestlé | CONSUMO   | chocolate-nestle.jpg
```

### Importas:
```bash
python manage.py import_excel data/Export.xls
```

### ¡Resultado!
```
http://127.0.0.1:8000/
├── Producto 1: Leche Alpina [IMG: leche-alpina.png] ✅
├── Producto 2: Café Nescafé [IMG: cafe-nescafe.jpg] ✅
├── Producto 3: Agua Cristalina [IMG: agua-cristalina.png] ✅
└── Producto 4: Chocolate Nestlé [IMG: chocolate-nestle.jpg] ✅
```

---

## 🆘 SI ALGO NO FUNCIONA

### Problema: "La imagen no aparece"

**Checklist:**
```
☐ ¿El archivo existe en media/productos/?
☐ ¿El nombre en Excel coincide exactamente?
☐ ¿El nombre no tiene espacios ni caracteres especiales?
☐ ¿Ejecutaste el import_excel?
☐ ¿Limpiaste caché navegador (Ctrl+F5)?
```

### Problema: "No puedo copiar archivos (permiso denegado)"

```bash
# PowerShell como Administrador:
icacls "c:\xampp\htdocs\Supermercado\media" /grant Users:F /t
```

### Problema: "El import dice que no encuentra la imagen"

```
Abre el archivo generado, verifica que la columna "imagen" 
tenga exactamente el nombre del archivo en media/productos/
```

---

## 📍 RESUMEN: DÓNDE COPIAR

**Carpeta de destino:**
```
C:\xampp\htdocs\Supermercado\media\productos\
```

**En Explorador Windows:**
```
1. C: → xampp → htdocs → Supermercado → media → productos
2. (Aquí pegas tus imágenes)
3. ¡Listo!
```

**Rutas alternativas correctas:**
```
✓ C:\xampp\htdocs\Supermercado\media\productos\
✓ c:\xampp\htdocs\Supermercado\media\productos\
✓ Supermercado → media → productos (desde raíz del proyecto)
```

---

**¡Ya sabes dónde poner las imágenes! 📸**

Próximo paso: Copia tus productos `.png` o `.jpg` a esa carpeta y importa desde Excel.

Versión: 3.0  
Fecha: 4 de Febrero, 2026
