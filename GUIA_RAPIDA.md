# 🚀 GUÍA RÁPIDA - Supermercado Yaruquíes

## 📦 INSTALACIÓN Y CONFIGURACIÓN

### 1️⃣ Instalar dependencias
```bash
cd c:\xampp\htdocs\Supermercado
pip install -r requirements.txt
```

### 2️⃣ Copiar imágenes de productos
Coloca tus archivos de imagen en:
```
c:\xampp\htdocs\Supermercado\media\productos\
```

**Formatos soportados:** JPG, PNG, JPEG, GIF, WebP

### 3️⃣ Importar productos desde Excel
```bash
python manage.py import_excel data/Export.xls
```

**Opción:** Para actualizar productos existentes
```bash
python manage.py import_excel data/Export.xls --actualizar
```

### 4️⃣ Iniciar servidor Django
```bash
python manage.py runserver 127.0.0.1:8000
```

---

## 🌐 URLs DEL SITIO

| URL | Descripción |
|-----|-------------|
| `http://127.0.0.1:8000/` | Página principal con productos destacados |
| `http://127.0.0.1:8000/categoria/consumo/` | Categoría: Consumo |
| `http://127.0.0.1:8000/categoria/limpieza-y-hogar/` | Categoría: Limpieza y Hogar |
| `http://127.0.0.1:8000/categoria/bebidas/` | Categoría: Bebidas |
| `http://127.0.0.1:8000/categoria/congelados/` | Categoría: Congelados |
| `http://127.0.0.1:8000/categoria/confiteria/` | Categoría: Confitería |
| `http://127.0.0.1:8000/quienes-somos/` | Información institucional |
| `http://127.0.0.1:8000/carrito/` | Carrito de compras |
| `http://127.0.0.1:8000/admin/` | Panel de administración Django |

---

## 📊 ESTRUCTURA EXCEL REQUERIDA

Tu archivo `data/Export.xls` debe tener estas columnas:

```
A: Código del Producto (ej: 7861038005138)
B: Código de Referencia (opcional)
C: Nombre/Descripción (ej: TIPS BAÑO PASTILLA 90G)
D: Categoría (CONSUMO, LIMPIEZA Y HOGAR, BEBIDAS, CONGELADOS, CONFITERIA)
E: Grupo (ej: CUIDADO PERSONAL)
F: Línea de Negocio (ej: General)
G: Stock (número entero, ej: 150)
H: Precio Normal (decimal, ej: 1.25)
I: Precio Oferta (opcional, ej: 1.00)
J: Costo Unitario (decimal, ej: 0.75)
K: Nombre de Imagen (solo el nombre, ej: producto1.jpg)
```

**Primera fila:** Encabezados (se salta automáticamente)

---

## 🖼️ GESTIÓN DE IMÁGENES

### Ubicación:
```
media/
└── productos/
    ├── tips_banio.jpg
    ├── alpina_leche.png
    ├── jolly_chocolate.jpg
    └── ... más imágenes
```

### En el Excel:
Escribe solo el nombre del archivo en la columna K:
```
tips_banio.jpg
alpina_leche.png
jolly_chocolate.jpg
```

El script busca automáticamente en `media/productos/`

---

## ✨ CARACTERÍSTICAS IMPLEMENTADAS

✅ **Importación flexible** - Soporta .xlsx y .xls
✅ **Validación de datos** - No guarda registros inválidos
✅ **Mapeo inteligente** - Categoriza automáticamente
✅ **Actualización** - Opción para actualizar productos existentes
✅ **Manejo de imágenes** - Asigna automáticamente si existen
✅ **Reportes detallados** - Muestra qué se importó y qué falló
✅ **5 Categorías dinámicas** - Consumo, Limpieza, Bebidas, Congelados, Confitería
✅ **Quiénes Somos mejorado** - Información institucional completa
✅ **Interfaz visual intacta** - Sin daños en diseño

---

## 📁 ARCHIVOS IMPORTANTES

### Recién Creados:
- `INSTRUCCIONES_IMPORTAR_EXCEL.md` - Guía completa
- `CAMBIOS_REALIZADOS_2026_02_04.md` - Resumen de cambios
- `core/management/commands/import_excel.py` - Script de importación
- `requirements.txt` - Dependencias Python

### Modificados:
- `core/templates/quienes_somos.html` - Contenido renovado
- `core/static/css/main.css` - Estilos mejorados

### Sin cambios (pero funcionales):
- `core/views.py` - URLs y vistas ya existentes
- `supermercado/urls.py` - Rutas ya configuradas
- `core/models.py` - Modelo de Producto

---

## 🐛 SOLUCIÓN RÁPIDA DE PROBLEMAS

### ❌ "ModuleNotFoundError: No module named 'openpyxl'"
```bash
pip install openpyxl xlrd
```

### ❌ "Las imágenes no aparecen"
- Verifica que el archivo exista en `media/productos/`
- Comprueba que el nombre coincida exactamente con lo que está en Excel
- Re-ejecuta: `python manage.py import_excel data/Export.xls --actualizar`

### ❌ "No puedo acceder a http://127.0.0.1:8000"
```bash
python manage.py runserver 127.0.0.1:8000
```

### ❌ "Error de permisos al guardar imágenes"
Asegúrate que la carpeta `media/productos/` tiene permisos de lectura/escritura.

---

## 📈 FLUJO COMPLETO (5 MINUTOS)

```bash
# 1. Ir al directorio del proyecto
cd c:\xampp\htdocs\Supermercado

# 2. Instalar dependencias (primera vez)
pip install -r requirements.txt

# 3. Copiar imágenes a media/productos/ (si aún no lo has hecho)
# (usa el explorador de Windows para esto)

# 4. Importar productos
python manage.py import_excel data/Export.xls

# 5. Iniciar servidor
python manage.py runserver 127.0.0.1:8000

# 6. Abrir en navegador
# http://127.0.0.1:8000
```

---

## 📱 CATEGORÍAS DISPONIBLES

1. **Consumo**
   - URL: `/categoria/consumo/`
   - Incluye: Abarrotes, alimentos, despensa

2. **Limpieza y Hogar**
   - URL: `/categoria/limpieza-y-hogar/`
   - Incluye: Limpieza, higiene, ferretería

3. **Bebidas**
   - URL: `/categoria/bebidas/`
   - Incluye: Refrescos, jugos, bebidas alcohólicas

4. **Congelados**
   - URL: `/categoria/congelados/`
   - Incluye: Carnes, pescados, helados, congelados

5. **Confitería**
   - URL: `/categoria/confiteria/`
   - Incluye: Dulces, chocolates, snacks, golosinas

---

## 🎯 PRÓXIMOS PASOS

1. ✅ Instalar dependencias
2. ✅ Preparar archivo Excel con estructura correcta
3. ✅ Copiar imágenes a `media/productos/`
4. ✅ Ejecutar importación
5. ✅ Verificar en navegador
6. ⏳ Personalizar información (WhatsApp, contacto, etc.)

---

**¡Tu Supermercado Yaruquíes está listo para funcionar!** 🛒
