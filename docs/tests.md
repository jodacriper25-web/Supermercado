# Pruebas y verificación

Incluye scripts y pruebas unitarias básicas.

Requisitos:
- XAMPP con Apache y MySQL corriendo
- Composer (para instalar dependencias como TCPDF y PHPUnit)

Instalación de dependencias:
1. Desde la raíz del proyecto, ejecuta:
   - `composer install`

Pruebas rápidas (scripts CLI):
- `php scripts/test_api.php` — valida endpoints públicos básicos
- `php scripts/test_checkout.php` — registra usuario, inicia sesión y realiza checkout (usa cookies)

Pruebas unitarias con PHPUnit:
1. Instala dependencias de desarrollo: `composer install` (incluye `phpunit/phpunit` si está configurado)
2. Levanta el servidor PHP integrado para que la API esté disponible en `TEST_BASE`:

   - `php -S 127.0.0.1:8000 -t ./backend/public`

   O mantén tu servidor local con la ruta `/Supermercado/backend/public/api.php`.

3. Ejecuta las pruebas (por defecto usan la variable `TEST_BASE` o `http://localhost/Supermercado/backend/public/api.php`):

   - `TEST_BASE=http://127.0.0.1:8000/api.php vendor/bin/phpunit`
   - o `composer test` (si está configurado)


Notas:
- Las pruebas HTTP esperan que el proyecto esté accesible en `http://localhost/Supermercado/`.
- Para pruebas que requieren credenciales admin, primero crea un admin con `php scripts/create_admin.php` y usa `admin/login.php` desde el navegador, o amplia los tests para manejar login y enviar cookies/CSRF a través de cURL.
