<?php
// Script CLI para probar flujo: registro -> login -> checkout (usa cURL con cookies)
$base = 'http://localhost/Supermercado/backend/public/api.php';

function curl_json($url, $method='GET', $data=null, $cookiefile=null, $headers=[]){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $defaultHeaders = ['Content-Type: application/json'];
    foreach($headers as $h) $defaultHeaders[] = $h;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
    if ($cookiefile){ curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile); curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile); }
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    if ($res === false) { echo "cURL error: " . curl_error($ch) . "\n"; }
    curl_close($ch);
    return [$res, $info];
}

$cookie = sys_get_temp_dir() . '/supermercado_test_cookies.txt';
$email = 'test+' . time() . '@example.com';
$password = 'Pass1234';

echo "1) Registro de usuario ($email)\n";
list($res,$info) = curl_json($base . '?action=register', 'POST', ['name'=>'Test User','email'=>$email,'password'=>$password], $cookie);
$j = json_decode($res, true);
if (!($j && isset($j['id']))) { echo "Registro falló: $res\n"; exit(1); }
echo "Registro OK (id={$j['id']})\n";

echo "2) Login de usuario\n";
list($res,$info) = curl_json($base . '?action=login', 'POST', ['email'=>$email,'password'=>$password], $cookie);
$j = json_decode($res, true);
if (!($j && $j['success'])) { echo "Login falló: $res\n"; exit(1); }
$csrf = $j['csrf'] ?? '';
if (!$csrf) { echo "No se recibió CSRF\n"; }
echo "Login OK, CSRF recibido\n";

echo "3) Tomar producto para el pedido\n";
list($res,$info) = curl_json($base . '?action=products&page=1&per_page=1', 'GET', null, $cookie);
$j = json_decode($res, true);
if (!($j && isset($j['products'][0]['id']))) { echo "No hay productos disponibles: $res\n"; exit(1); }
$prod = $j['products'][0];
$items = [['product_id'=> (int)$prod['id'], 'quantity'=>1]];

// Crear un cupón temporal para la prueba
require_once __DIR__ . '/../backend/src/db.php';
$code = 'CLI' . time();
$expires = date('Y-m-d', strtotime('+1 day'));
$stmt = $pdo->prepare('INSERT INTO coupons (code,type,value,active,expires_at) VALUES (?,?,?,?,?)');
$stmt->execute([$code,'percent',10,1,$expires]);
echo "Cupón de prueba creado: $code\n";

// Validar cupón
list($cres,$cinfo) = curl_json($base . '?action=coupon_validate&code=' . urlencode($code) . '&total=100', 'GET', null, $cookie);
$cj = json_decode($cres, true);
if (!($cj && $cj['valid'])) { echo "Validación de cupón falló: $cres\n"; }

echo "4) Crear pedido\n";
list($res,$info) = curl_json($base . '?action=create_order', 'POST', ['items'=>$items,'total'=>0,'coupon_code'=>$code], $cookie, ['X-CSRF-Token: ' . $csrf]);
$j = json_decode($res, true);
if (!($j && $j['success'])) { echo "Crear pedido falló: $res\n"; exit(1); }
echo "Pedido creado OK (order_id={$j['order_id']}), factura: " . ($j['invoice'] ?? 'ninguna') . "\n";

// Verificar en DB (opcional)
require_once __DIR__ . '/../backend/src/db.php';
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$j['order_id']]);
$order = $stmt->fetch();
if ($order) echo "Verificado en DB: pedido total={$order['total']} estado={$order['status']}\n"; else echo "Pedido no encontrado en DB\n";

echo "Prueba checkout completada.\n";
