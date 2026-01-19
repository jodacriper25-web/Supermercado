<?php
ob_start();
session_start();

/* =======================
   HEADERS DE SEGURIDAD
======================= */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
header('X-XSS-Protection: 1; mode=block');

/* =======================
   DEPENDENCIAS
======================= */
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/admin_auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/models.php';
require_once __DIR__ . '/../src/invoice.php';

/* =======================
   UTILIDADES
======================= */
function json_ok($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function json_err($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

function get_json_input() {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    if ($data === null) json_err('JSON inválido');
    return $data;
}

function require_csrf() {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validate_csrf($token)) json_err('CSRF inválido', 403);
}

/* =======================
   ROUTER
======================= */
$action = $_GET['action'] ?? '';

switch ($action) {

/* =======================
   AUTH USUARIO
======================= */
case 'register':
    $d = get_json_input();
    if (empty($d['name']) || empty($d['email']) || empty($d['password'])) {
        json_err('Campos incompletos');
    }
    $id = registerUser($d['name'], $d['email'], $d['password']);
    json_ok(['id' => $id]);
    break;

case 'login':
    $d = get_json_input();
    $user = loginUser($d['email'] ?? '', $d['password'] ?? '');
    if (!$user) json_err('Credenciales inválidas', 401);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    json_ok([
        'user' => $_SESSION['user'],
        'csrf' => generate_csrf()
    ]);
    break;

/* =======================
   AUTH ADMIN
======================= */
case 'admin_login':
    $d = get_json_input();
    if (!adminLogin($d['email'] ?? '', $d['password'] ?? '')) {
        json_err('Credenciales inválidas', 401);
    }
    json_ok(['csrf' => generate_csrf()]);
    break;

/* =======================
   PRODUCTOS
======================= */
case 'products':
    $page = max(1, intval($_GET['page'] ?? 1));
    $per = max(1, min(100, intval($_GET['per_page'] ?? 20)));
    $filters = $_GET;
    $res = getProducts($filters, $page, $per);
    json_ok([
        'products' => $res['data'],
        'total' => $res['total'],
        'page' => $page
    ]);
    break;

case 'product':
    $id = intval($_GET['id'] ?? 0);
    json_ok(['product' => getProduct($id)]);
    break;

case 'product_create':
    requireAdmin();
    require_csrf();
    $id = createProduct(get_json_input());
    json_ok(['id' => $id]);
    break;

case 'product_update':
    requireAdmin();
    require_csrf();
    $d = get_json_input();
    updateProduct($d['id'], $d);
    json_ok();
    break;

case 'product_delete':
    requireAdmin();
    require_csrf();
    deleteProduct(intval($_GET['id'] ?? 0));
    json_ok();
    break;

/* =======================
   PEDIDOS
======================= */
case 'create_order':
    if (empty($_SESSION['user'])) json_err('No autenticado', 403);
    require_csrf();
    $d = get_json_input();
    $orderId = createOrder($_SESSION['user']['id'], $d['items'], $d['total'] ?? null, $d['coupon_code'] ?? null);
    json_ok(['order_id' => $orderId]);
    break;

case 'orders':
    requireAdmin();
    $res = getAllOrders(1, 100, $_GET);
    json_ok($res);
    break;

/* =======================
   CUPONES
======================= */
case 'coupon_validate':
    $code = $_GET['code'] ?? '';
    $total = floatval($_GET['total'] ?? 0);
    if (!$code) json_err('Código vacío');

    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code=? AND active=1 LIMIT 1");
    $stmt->execute([$code]);
    $c = $stmt->fetch();
    if (!$c) json_err('Cupón inválido');

    if ($c['expires_at'] && strtotime($c['expires_at']) < time()) {
        json_err('Cupón expirado');
    }

    $discount = $c['type'] === 'percent'
        ? ($total * $c['value']) / 100
        : $c['value'];

    $discount = min($discount, $total);

    json_ok([
        'discount' => round($discount, 2),
        'new_total' => round($total - $discount, 2)
    ]);
    break;

/* =======================
   UPLOAD IMAGEN
======================= */
case 'upload_image':
    requireAdmin();
    require_csrf();
    if (empty($_FILES['image'])) json_err('Archivo faltante');

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $name = uniqid('prod_') . '.' . $ext;
    $dir = __DIR__ . '/../../frontend/public/assets/img/products';

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    move_uploaded_file($_FILES['image']['tmp_name'], "$dir/$name");

    json_ok(['file' => $name]);
    break;

/* =======================
   DEFAULT
======================= */
default:
    json_err('Acción no válida');
}
