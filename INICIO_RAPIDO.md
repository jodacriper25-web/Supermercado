# ⚡ INICIO RÁPIDO - 5 MINUTOS

## 🎯 Tu misión: Poner el Supermercado en línea

Sigue estos pasos exactos en este orden:

---

## PASO 1️⃣: Instalar Dependencias (2 minutos)

**Abre PowerShell o CMD en la carpeta del proyecto:**

```powershell
cd c:\xampp\htdocs\Supermercado
```

**Instala las librerías necesarias:**

```powershell
pip install -r requirements.txt
```

✅ Deberías ver instalaciones de Django, Pillow, openpyxl, xlrd

---

## PASO 2️⃣: Preparar Imágenes (1 minuto)

**Copia tus imágenes de productos aquí:**
```
c:\xampp\htdocs\Supermercado\media\productos\
```

**Formatos:** JPG, PNG, GIF, WebP

**Ejemplo de estructura:**
```
media/
└── productos/
    ├── tips_banio.jpg
    ├── alpina_leche.png
    ├── jolly_chocolate.jpg
    └── ... más imágenes
```

✅ Si aún no tienes imágenes, puedes hacerlo después

---

## PASO 3️⃣: Importar Productos Excel (1 minuto)

**En la misma carpeta, ejecuta:**

```powershell
python manage.py import_excel data/Export.xls
```

**Deberías ver algo como esto:**
```
✓ Fila 2: Producto creado - TIPS BAÑO PASTILLA MANZANA 90G
✓ Fila 3: Producto creado - ALPINA LECHE DURAZNO 140G
↻ Fila 4: Producto actualizado - ARCOR FRUTILLAS ACIDAS 150G

==================================================
✓ Productos importados: 2
↻ Productos actualizados: 1
✗ Errores: 0
==================================================
```

✅ Tus productos están ahora en la BD

---

## PASO 4️⃣: Iniciar el Servidor (1 minuto)

**En la misma ventana PowerShell:**

```powershell
python manage.py runserver 127.0.0.1:8000
```

**Deberías ver:**
```
Starting development server at http://127.0.0.1:8000/
Quit the server with CTRL-BREAK.
```

✅ El servidor está funcionando

---

## PASO 5️⃣: Abre tu Navegador (Inmediato)

**Copia y pega en la barra de direcciones:**
```
http://127.0.0.1:8000
```

**O para admin:**
```
http://127.0.0.1:8000/admin
```

✅ ¡Tu supermercado está en línea! 🎉

---

## 📱 Prueba las Categorías

Haz clic en estos enlaces para ver los productos filtrados:

- [Consumo](http://127.0.0.1:8000/categoria/consumo/)
- [Limpieza y Hogar](http://127.0.0.1:8000/categoria/limpieza-y-hogar/)
- [Bebidas](http://127.0.0.1:8000/categoria/bebidas/)
- [Congelados](http://127.0.0.1:8000/categoria/congelados/)
- [Confitería](http://127.0.0.1:8000/categoria/confiteria/)
- [Quiénes Somos](http://127.0.0.1:8000/quienes-somos/)

---

## ⚙️ Si algo NO funciona

### Error: "No module named 'openpyxl'"
```powershell
pip install openpyxl xlrd
```

### Error: "File not found: data/Export.xls"
- Asegúrate que el archivo Excel está en `c:\xampp\htdocs\Supermercado\data\`

### Las imágenes no aparecen
- Verifica que están en `media/productos/`
- Que el nombre coincide exactamente con lo que está en Excel (incluyendo extensión)

### No veo los productos
- Comprueba que el Excel tiene la estructura correcta
- Ve al admin (`/admin`) para verificar que están guardados
- Comprueba que el campo `activo` es TRUE

---

## 📖 Documentación Completa

Si necesitas más detalles:

- **GUIA_RAPIDA.md** - Instrucciones paso a paso completes
- **INSTRUCCIONES_IMPORTAR_EXCEL.md** - Todo sobre importación Excel
- **CAMBIOS_REALIZADOS_2026_02_04.md** - Qué se hizo exactamente
- **PROYECTO_COMPLETADO.md** - Resumen general del proyecto

---

## 🎯 Próximos Pasos Opcionales

1. **Agregar más productos** - Actualiza Excel y ejecuta nuevamente con `--actualizar`
2. **Personalizar contacto** - Edita `core/templates/base.html` y `quienes_somos.html`
3. **Cambiar números de WhatsApp** - Busca el número en los templates
4. **Agregar promociones** - Usa el campo `precio_oferta` en Excel

---

## 💡 Tips Útiles

- **Mantén PowerShell abierta** - No cierres la ventana mientras desarrollas
- **Contraseña admin** - Crea una con `python manage.py createsuperuser`
- **Cambios en código** - El servidor se recarga automáticamente
- **Base de datos** - Todo se guarda en `db.sqlite3` (no borres este archivo)

---

## ✨ ¡Listo para Producción?

Cuando termines el desarrollo:
1. Guarda una copia de seguridad de `db.sqlite3`
2. Asegúrate que `DEBUG = False` en `settings.py`
3. Configura un servidor web como Gunicorn o uWSGI
4. Usa un dominio real en ALLOWED_HOSTS

---

## 📞 Soporte

Si algo no funciona:
1. Lee el error completo en PowerShell
2. Consulta GUIA_RAPIDA.md o INSTRUCCIONES_IMPORTAR_EXCEL.md
3. Verifica que todos los archivos existan en sus carpetas correctas
4. Ejecuta `python verificar_instalacion.py` para diagnosticar

---

**¡Bienvenido a tu Supermercado Yaruquíes en línea!** 🛒✨
