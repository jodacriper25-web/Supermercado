# Administración

Acceso: `http://localhost/Supermercado/admin/login.php`

- Inicio de sesión de administrador: `POST /backend/public/api.php?action=admin_login` con JSON `{email,password}`. Devuelve un `csrf` y crea sesión.
- Endpoints principales (requieren admin + CSRF en cabecera `X-CSRF-Token`):
  - `action=orders` - listar pedidos (acepta `page` y `per_page`, devuelve `orders` y `total`)
  - `action=order&id=` - detalles de pedido
  - `action=update_order_status` - actualizar estado (POST, requiere admin + CSRF)
  - `action=product_create|product_update|product_delete` - gestión de productos (POST, requiere admin + CSRF)
  - `action=reports&start=YYYY-MM-DD&end=YYYY-MM-DD` - reportes (exportable a CSV con `&format=csv` y a PDF con `&format=pdf`)
  - `action=list_coupons|coupon_create|coupon_update|coupon_delete` - gestión de cupones (POST, requiere admin + CSRF). También existe `action=coupon_validate&code=CODE&total=TOTAL` para validar un cupón de forma pública.
  
Nota: la paginación en endpoints como `products` y `orders` se controla con `page` y `per_page`. CSV/PDF requieren sesión admin y token CSRF en la cabecera `X-CSRF-Token`.
Nota: los CSRF tokens se devuelven al hacer login admin y se deben enviar en la cabecera `X-CSRF-Token` para acciones sensibles.