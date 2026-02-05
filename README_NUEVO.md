# 🛒 Supermercado Yaruquíes - Sistema E-Commerce

**Versión:** 1.0  
**Última actualización:** 4 de Febrero, 2026  
**Estado:** ✅ Completado y funcional

---

## 📋 Descripción General

Sistema de comercio electrónico completo para el **Supermercado Yaruquíes** en Riobamba, Ecuador. Construido con Django 4.2, Bootstrap 5 y SQLite.

**Características principales:**
- ✅ Catálogo de productos dinámico con 5 categorías
- ✅ Importación de productos desde Excel (.xlsx y .xls)
- ✅ Gestión de imágenes de productos
- ✅ Carrito de compras con localStorage
- ✅ Sistema de autenticación de usuarios
- ✅ Panel de administración Django
- ✅ Página institucional "Quiénes Somos"
- ✅ Interfaz responsive y moderna

---

## 🚀 Inicio Rápido (5 minutos)

### 1. Instalar Dependencias
```bash
cd c:\xampp\htdocs\Supermercado
pip install -r requirements.txt
```

### 2. Preparar Imágenes
Coloca las imágenes de productos en:
```
media/productos/
```

### 3. Importar Productos desde Excel
```bash
python manage.py import_excel data/Export.xls
```

### 4. Iniciar Servidor
```bash
python manage.py runserver 127.0.0.1:8000
```

### 5. Acceder
Abre tu navegador: `http://127.0.0.1:8000`

---

## 📁 Estructura del Proyecto

```
Supermercado/
├── manage.py                          # Gestor de Django
├── requirements.txt                   # Dependencias
├── db.sqlite3                         # Base de datos
│
├── supermercado/                      # Configuración principal
│   ├── settings.py
│   ├── urls.py
│   └── wsgi.py
│
├── core/                              # Aplicación principal
│   ├── models.py                      # Modelos de BD
│   ├── views.py                       # Vistas
│   ├── forms.py                       # Formularios
│   │
│   ├── management/commands/
│   │   └── import_excel.py            # ✨ Script de importación
│   │
│   ├── templates/                     # Templates HTML
│   │   ├── index.html
│   │   ├── category.html
│   │   ├── quienes_somos.html
│   │   ├── cart_detail.html
│   │   └── ...
│   │
│   ├── static/
│   │   ├── css/main.css
│   │   ├── js/cart.js
│   │   └── img/
│   │
│   └── migrations/                    # Migraciones de BD
│
├── media/
│   └── productos/                     # Imágenes de productos
│
├── data/
│   ├── Export.xls                     # Archivo Excel con productos
│   └── Export.xml
│
└── docs/
    ├── INICIO_RAPIDO.md               # Inicio en 5 minutos
    ├── GUIA_RAPIDA.md                 # Guía rápida
    ├── INSTRUCCIONES_IMPORTAR_EXCEL.md # Detalles de importación
    └── PROYECTO_COMPLETADO.md         # Resumen completo
```

---

## 📚 Documentación

- **[INICIO_RAPIDO.md](INICIO_RAPIDO.md)** ⚡ - Los 5 pasos básicos (EMPIEZA AQUÍ)
- **[GUIA_RAPIDA.md](GUIA_RAPIDA.md)** 📖 - Referencia rápida
- **[INSTRUCCIONES_IMPORTAR_EXCEL.md](INSTRUCCIONES_IMPORTAR_EXCEL.md)** 📊 - Cómo importar productos
- **[CAMBIOS_REALIZADOS_2026_02_04.md](CAMBIOS_REALIZADOS_2026_02_04.md)** 📋 - Resumen de cambios
- **[PROYECTO_COMPLETADO.md](PROYECTO_COMPLETADO.md)** ✅ - Estado completo del proyecto

---

## 🔧 Requisitos Técnicos

- **Python:** 3.8+
- **Django:** 4.2.0
- **Base de datos:** SQLite (incluida)
- **Servidor:** Local (desarrollo) o Gunicorn/Render (producción)

### Dependencias Python
```
Django==4.2.0
Pillow==10.0.0
openpyxl==3.1.2
xlrd==2.0.1
```

---

## 📊 Importación de Productos

El proyecto incluye un potente script para importar productos desde Excel:

```bash
# Importación básica
python manage.py import_excel data/Export.xls

# Importación con actualización de existentes
python manage.py import_excel data/Export.xls --actualizar
```

**El script:**
- ✅ Lee archivos .xlsx y .xls
- ✅ Valida automáticamente
- ✅ Mapea a 5 categorías
- ✅ Asigna imágenes si existen
- ✅ Genera reportes

Ver [INSTRUCCIONES_IMPORTAR_EXCEL.md](INSTRUCCIONES_IMPORTAR_EXCEL.md) para detalles completos.

---

## 🌐 URLs del Sitio

| URL | Descripción |
|-----|-------------|
| `/` | Página principal |
| `/categoria/consumo/` | Categoría: Consumo |
| `/categoria/limpieza-y-hogar/` | Categoría: Limpieza |
| `/categoria/bebidas/` | Categoría: Bebidas |
| `/categoria/congelados/` | Categoría: Congelados |
| `/categoria/confiteria/` | Categoría: Confitería |
| `/quienes-somos/` | Información institucional |
| `/carrito/` | Carrito de compras |
| `/checkout/` | Finalizar compra |
| `/register/` | Registrarse |
| `/login/` | Ingresar |
| `/admin/` | Panel de administración |

---

## 🎯 Categorías de Productos

El sistema tiene 5 categorías principales:

1. **CONSUMO** - Abarrotes, alimentos, despensa
2. **LIMPIEZA Y HOGAR** - Artículos de limpieza, higiene
3. **BEBIDAS** - Refrescos, jugos, bebidas alcohólicas
4. **CONGELADOS** - Carnes, pescados, helados
5. **CONFITERIA** - Dulces, chocolates, snacks

---

## 📸 Gestión de Imágenes

### Ubicación
Las imágenes deben estar en: `media/productos/`

### Formato del Excel (Columna K)
```
tips_banio.jpg
alpina_leche.png
jolly_chocolate.jpg
```

El script busca automáticamente en `media/productos/`

---

## 🔐 Seguridad

⚠️ **Para Desarrollo:**
- DEBUG = True
- SECRET_KEY temporal

✅ **Para Producción:**
- Cambiar DEBUG = False
- Generar nuevo SECRET_KEY
- Configurar ALLOWED_HOSTS
- Usar HTTPS
- Configurar base de datos PostgreSQL

---

## 💻 Desarrollo

### Crear usuario admin
```bash
python manage.py createsuperuser
```

### Ejecutar migraciones
```bash
python manage.py migrate
python manage.py makemigrations
```

### Acceder a admin
```
http://127.0.0.1:8000/admin
```

---

## 🌐 Despliegue en Producción

### Opción 1: Render.com
```bash
# Build
pip install -r requirements.txt
python manage.py migrate
python manage.py collectstatic --noinput

# Start
gunicorn supermercado.wsgi
```

### Opción 2: PythonAnywhere
- Sube el proyecto
- Configura el WSGI
- Asegura media y static

### Opción 3: Servidor Propio
- Instala Gunicorn: `pip install gunicorn`
- Configura Nginx
- SSL con Let's Encrypt

---

## 📞 Contacto y Soporte

**Supermercado Yaruquíes**
- 📍 Yaruquíes, Riobamba, Ecuador
- 📱 WhatsApp: +593 99 999 9999
- 📧 Email: hola@supermercadoyaruquies.com

---

## 📝 Notas Importantes

- ✅ Toda la interfaz visual está intacta
- ✅ No se han dañado funcionalidades existentes
- ✅ Compatible con navegadores modernos
- ✅ Responsive para móviles
- ✅ Base de datos lista para datos reales

---

## 🎉 Estado del Proyecto

**✅ COMPLETADO Y FUNCIONAL**

Todas las tareas requeridas han sido completadas:
- ✅ Script de importación desde Excel
- ✅ Categorías dinámicas (5 líneas)
- ✅ Gestión de imágenes
- ✅ Página Quiénes Somos mejorada
- ✅ Interfaz visual intacta

---

## 📄 Licencia

Este proyecto es propiedad de Supermercado Yaruquíes.

---

**Última actualización:** 4 de Febrero, 2026
