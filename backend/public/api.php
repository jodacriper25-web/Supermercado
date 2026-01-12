<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
header('X-XSS-Protection: 1; mode=block');
session_start();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/models.php';
require_once __DIR__ . '/../src/admin_auth.php';
require_once __DIR__ . '/../src/invoice.php';

$action = $_GET['action'] ?? '';

function require_csrf(){
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validate_csrf($token)) { http_response_code(403); echo json_encode(['error'=>'Invalid CSRF']); exit; }
}

function get_json_input(){
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if ($raw !== '' && $data === null && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false){ http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }
    return $data ?? [];
}

function json_ok($data){ echo json_encode(array_merge(['success'=>true], $data)); }
function json_err($msg, $code=400){ http_response_code($code); echo json_encode(['success'=>false,'error'=>$msg]); }


switch ($action) {
    case 'register':
        $data = get_json_input();
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) { json_err('Missing fields'); }
        $id = registerUser($data['name'], $data['email'], $data['password']);
        json_ok(['id' => $id]);
        break;

    case 'login':
        $data = get_json_input();
        $user = loginUser($data['email'] ?? '', $data['password'] ?? '');
        if ($user) {
            // Crear sesión simple y CSRF token
            $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
            $token = generate_csrf();
            json_ok(['user' => ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']], 'csrf' => $token]);
        } else {
            json_err('Invalid credentials', 401);
        }
        break;

    case 'products':
        $filters = [];
        if (isset($_GET['category_id'])) $filters['category_id'] = intval($_GET['category_id']);
        if (isset($_GET['min_price'])) $filters['min_price'] = floatval($_GET['min_price']);
        if (isset($_GET['max_price'])) $filters['max_price'] = floatval($_GET['max_price']);
        if (isset($_GET['available'])) $filters['available'] = true;
        if (isset($_GET['q'])) $filters['q'] = trim($_GET['q']);
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = max(1, min(100, intval($_GET['per_page'] ?? 20)));
        $res = getProducts($filters, $page, $perPage);
        if (isset($_GET['format']) && $_GET['format'] === 'csv'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="products_export_'.date('Ymd').'.csv"');
            $out = fopen('php://output','w');
            fputcsv($out, ['id','category','name','price','stock','featured']);
            foreach($res['data'] as $p){ fputcsv($out, [$p['id'],$p['category'],$p['name'],$p['price'],$p['stock'],$p['featured']]); }
            exit;
        }
        json_ok(['products' => $res['data'], 'total' => $res['total'], 'page' => $page, 'per_page' => $perPage]);
        break;

    case 'product':
        $id = intval($_GET['id'] ?? 0);
        $product = getProduct($id);
        echo json_encode(['product' => $product]);
        break;

    case 'promotions':
        // Devuelve productos destacados (featured)
        $limit = max(1, min(10, intval($_GET['limit'] ?? 5)));
        $stmt = $pdo->prepare('SELECT p.*, c.name as category FROM products p JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 ORDER BY p.id DESC LIMIT ?');
        $stmt->execute([$limit]);
        $promos = $stmt->fetchAll();
        echo json_encode(['promotions' => $promos]);
        break;

    case 'create_order':
        $data = get_json_input();
        // Validar autenticación y CSRF
        if (empty($_SESSION['user'])){ json_err('Not authenticated',403); }
        require_csrf();
        if (empty($data['items']) || !is_array($data['items'])){ json_err('Invalid payload'); }
        $userId = $_SESSION['user']['id'];
        $couponCode = isset($data['coupon_code']) ? trim($data['coupon_code']) : null;
        $res = createOrder($userId, $data['items'], $data['total'] ?? null, $couponCode);
        if (is_array($res) && isset($res['error'])){ json_err($res['error']); }
        $orderId = $res;
        // Generar factura automática
        $invoiceNumber = getNextInvoiceNumber();
        $path = generateInvoicePdf($orderId, $invoiceNumber);
        // Enviar email de confirmación si es posible
        // Obtener datos completos del pedido para el email
        $order = getOrder($orderId);
        $stmt = $pdo->prepare('SELECT u.email, u.name FROM users u WHERE u.id = ?');
        $stmt->execute([$order['user_id']]);
        $usr = $stmt->fetch();
        if ($usr && !empty($usr['email'])){
            $body = "<h3>Gracias por su pedido (#{$orderId})</h3><p>Hemos recibido su pedido por un total de " . number_format($order['total'],2) . "</p>";
            if (!empty($order['coupon_code'])){ $body .= "<p>Cupón aplicado: " . htmlspecialchars($order['coupon_code']) . "</p>"; }
            // si se generó factura, agregar referencia
            if ($path) $body .= "<p>Factura: {$invoiceNumber}</p>";
            $body .= "<p>Detalles:</p><ul>";
            foreach($order['items'] as $oi){ $body .= "<li>".htmlspecialchars($oi['name'])." x " . intval($oi['quantity']) . " - $" . number_format($oi['price'],2) . "</li>"; }
            $body .= "</ul>";
            require_once __DIR__ . '/../src/mailer.php';
            sendOrderEmail($usr['email'], 'Confirmación de pedido #'.$orderId, $body);
        }
        json_ok(['order_id'=>$orderId,'invoice'=>$path ? $invoiceNumber : null]);
        break;

    case 'order':
        $id = intval($_GET['id'] ?? 0);
        $order = getOrder($id);
        echo json_encode(['order' => $order]);
        break;

    case 'orders':
        // Admin: listar todos los pedidos con filtros
        requireAdmin();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = max(1, min(200, intval($_GET['per_page'] ?? 50)));
        $filters = [];
        if (isset($_GET['q'])) $filters['q'] = trim($_GET['q']);
        if (isset($_GET['status'])) $filters['status'] = trim($_GET['status']);
        if (isset($_GET['start'])) $filters['start'] = trim($_GET['start']);
        if (isset($_GET['end'])) $filters['end'] = trim($_GET['end']);
        $res = getAllOrders($page, $perPage, $filters);
        if (isset($_GET['format']) && $_GET['format'] === 'csv'){
            // Return CSV of filtered orders (use a large limit)
            $all = getAllOrders(1, max(1000, $res['total']), $filters);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="orders_export_'.date('Ymd').'.csv"');
            $out = fopen('php://output','w');
            fputcsv($out, ['id','customer_name','email','total','status','created_at','invoice_number']);
            foreach($all['data'] as $o){ fputcsv($out, [$o['id'],$o['customer_name'],$o['email'],$o['total'],$o['status'],$o['created_at'], $o['invoice_number'] ?? '']); }
            exit;
        }
        json_ok(['orders' => $res['data'], 'total' => $res['total'], 'page' => $page, 'per_page' => $perPage]);
        break;

    case 'user_orders':
        if (empty($_SESSION['user'])){ http_response_code(403); echo json_encode(['error'=>'Not authenticated']); exit; }
        $orders = getUserOrders($_SESSION['user']['id']);
        echo json_encode(['orders' => $orders]);
        break;

    case 'update_order_status':
        // Admin only
        requireAdmin();
        require_csrf();
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['order_id']) || empty($data['status'])){ echo json_encode(['error'=>'Invalid payload']); exit; }
        $ok = updateOrderStatus(intval($data['order_id']), $data['status']);
        echo json_encode(['success' => (bool)$ok]);
        break;

    case 'reports':
        // Admin only
        requireAdmin();
        $start = $_GET['start'] ?? date('Y-01-01');
        $end = $_GET['end'] ?? date('Y-m-d');
        $report = salesReport($start.' 00:00:00', $end.' 23:59:59');
        if (isset($_GET['format']) && $_GET['format']==='csv'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="sales_report.csv"');
            $out = fopen('php://output','w');
            fputcsv($out, ['date','total','orders']);
            foreach($report as $r) fputcsv($out, [$r['date'],$r['total'],$r['orders']]);
            exit;
        }
        if (isset($_GET['format']) && $_GET['format']==='pdf'){
            // Generar PDF simple con TCPDF
            if (file_exists(__DIR__ . '/../../vendor/autoload.php')) require_once __DIR__ . '/../../vendor/autoload.php';
            if (!class_exists('TCPDF')){ http_response_code(500); echo json_encode(['error'=>'TCPDF not installed']); exit; }
            $pdf = new TCPDF();
            $pdf->AddPage();
            $html = "<h2>Reporte de ventas ({$start} - {$end})</h2><table border=1 cellpadding=4><thead><tr><th>Fecha</th><th>Total</th><th>Pedidos</th></tr></thead><tbody>";
            foreach($report as $r){ $html .= "<tr><td>{$r['date']}</td><td>{$r['total']}</td><td>{$r['orders']}</td></tr>"; }
            $html .= "</tbody></table>";
            $pdf->writeHTML($html);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="sales_report.pdf"');
            $pdf->Output('sales_report.pdf','I');
            exit;
        }
        echo json_encode(['report'=>$report]);
        break;

    case 'admin_login':
        $data = get_json_input();
        if (adminLogin($data['email'] ?? '', $data['password'] ?? '')){ json_ok(['csrf'=>generate_csrf()]); } else { json_err('Invalid credentials',401); }
        break;

    case 'upload_image':
        // Admin upload de imagenes (form-data)
        requireAdmin();
        require_csrf();
        if (empty($_FILES['image'])){ echo json_encode(['success'=>false,'error'=>'No file']); exit; }
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('prod_') . '.' . $ext;
        $dir = __DIR__ . '/../../frontend/public/assets/img/products';
        if (!is_dir($dir)) mkdir($dir,0755,true);
        $target = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) { echo json_encode(['success'=>false,'error'=>'Move failed']); exit; }
        echo json_encode(['success'=>true,'file'=>$name]);
        break;

    case 'product_create':
        requireAdmin();
        require_csrf();
        $data = get_json_input();
        $id = createProduct($data);
        json_ok(['id'=>$id]);
        break;

    case 'product_update':
        requireAdmin();
        require_csrf();
        $data = get_json_input();
        $ok = updateProduct(intval($data['id']), $data);
        json_ok([]);
        break;

    // Cupones (admin)
    case 'list_coupons':
        requireAdmin();
        $stmt = $pdo->query('SELECT * FROM coupons ORDER BY id DESC');
        $coupons = $stmt->fetchAll();
        if (isset($_GET['format']) && $_GET['format'] === 'csv'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="coupons_export_'.date('Ymd').'.csv"');
            $out = fopen('php://output','w');
            fputcsv($out, ['id','code','type','value','active','expires_at']);
            foreach($coupons as $c){ fputcsv($out, [$c['id'],$c['code'],$c['type'],$c['value'],$c['active'],$c['expires_at']]); }
            exit;
        }
        json_ok(['coupons'=>$coupons]);
        break;

    case 'get_coupon':
        requireAdmin();
        $id = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE id = ?'); $stmt->execute([$id]); $c = $stmt->fetch(); echo json_encode(['coupon'=>$c]);
        break;

    case 'coupon_create':
        requireAdmin(); require_csrf(); $data = get_json_input(); $stmt=$pdo->prepare('INSERT INTO coupons (code,type,value,active,expires_at) VALUES (?,?,?,?,?)'); $stmt->execute([$data['code'],$data['type'],$data['value'],$data['active'],$data['expires_at']]); json_ok(['id'=>$pdo->lastInsertId()]);
        break;

    case 'coupon_update':
        requireAdmin(); require_csrf(); $data = get_json_input(); $stmt=$pdo->prepare('UPDATE coupons SET code=?,type=?,value=?,active=?,expires_at=? WHERE id=?'); $stmt->execute([$data['code'],$data['type'],$data['value'],$data['active'],$data['expires_at'],$data['id']]); json_ok([]);
        break;

    case 'coupon_delete':
        requireAdmin(); require_csrf(); $id = intval($_GET['id'] ?? 0); $stmt=$pdo->prepare('DELETE FROM coupons WHERE id = ?'); $stmt->execute([$id]); echo json_encode(['success'=>true]);
        break;

    case 'coupon_validate':
        $code = $_GET['code'] ?? '';
        $total = floatval($_GET['total'] ?? 0);
        if (!$code){ echo json_encode(['valid'=>false,'error'=>'Missing code']); exit; }
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND active = 1 LIMIT 1'); $stmt->execute([$code]); $c = $stmt->fetch(); if (!$c){ echo json_encode(['valid'=>false,'error'=>'Invalid coupon']); exit; }
        if ($c['expires_at'] && strtotime($c['expires_at']) < time()){ echo json_encode(['valid'=>false,'error'=>'Coupon expired']); exit; }
        if ($c['type']==='percent'){ $discount = ($total * floatval($c['value']))/100.0; } else { $discount = floatval($c['value']); }
        $discount = min($discount, $total);
        echo json_encode(['valid'=>true,'discount'=>round($discount,2),'new_total'=>round($total-$discount,2),'coupon'=>$c]);
        break;

    case 'product_delete':
        requireAdmin();
        require_csrf();
        $id = intval($_GET['id'] ?? 0);
        $ok = deleteProduct($id);
        echo json_encode(['success'=>!!$ok]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
