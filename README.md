# Supermercado Yaruquíes

Proyecto web de ejemplo "Supermercado Yaruquíes" preparado para ejecutarse en XAMPP (entorno local) y diseñado con un stack PHP + JavaScript moderno. Incluye backend en PHP (con PDO, bcrypt, generación de PDFs), frontend responsivo con Bootstrap 5, y documentación para integración futura con Django.

## Resumen
- Backend: PHP (estructura modular con controladores y modelos), conexión PDO a MySQL
- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript (fetch, microinteracciones)
- Base de datos: MySQL (scripts de migración en `migrations/schema.sql`)
- Facturación: generación de PDF (recomendado: instalar `tecnickcom/tcpdf` via Composer)
- Seguridad: contraseñas con bcrypt, uso de prepared statements (PDO), tokens CSRF

Consulta `docs/installation.md` para pasos de instalación local en XAMPP.