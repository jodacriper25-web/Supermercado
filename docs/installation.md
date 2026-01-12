# Instalación local en XAMPP

1. Instala XAMPP y arranca Apache y MySQL.
2. Copia la carpeta `Supermercado` dentro de `C:\xampp\htdocs\`.
3. Crea una base de datos MySQL, por ejemplo `supermercado_db`.
4. Importa `migrations/schema.sql` usando phpMyAdmin o la línea de comandos.
5. Edita `backend/src/config.php` y ajusta las credenciales de BD.
6. (Opcional) Instala Composer y las dependencias:
   - `composer require tecnickcom/tcpdf`
   - (Recomendado para correos) `composer require phpmailer/phpmailer`
7. Crea un usuario administrador (opcional):
   - Puedes registrar un usuario en la tabla `users` y en phpMyAdmin cambiar su `role` a `admin`, o usar el script CLI `php scripts/create_admin.php <email> <name> <password>`.
8. Abre en tu navegador: `http://localhost/Supermercado/frontend/public/index.php` y `http://localhost/Supermercado/admin/index.php` para el panel de administración.

Nota: la integración con Django se describe en `docs/django_integration.md`.