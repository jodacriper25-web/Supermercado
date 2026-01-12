<?php
require_once __DIR__ . '/db.php';

// Productos
function getProducts($filters = [], $page = 1, $perPage = 20){
    global $pdo;
    $where = ' WHERE 1=1';
    $params = [];
    if (!empty($filters['category_id'])){ $where .= ' AND p.category_id = ?'; $params[] = $filters['category_id']; }
    if (!empty($filters['min_price'])){ $where .= ' AND p.price >= ?'; $params[] = $filters['min_price']; }
    if (!empty($filters['max_price'])){ $where .= ' AND p.price <= ?'; $params[] = $filters['max_price']; }
    if (!empty($filters['available'])){ $where .= ' AND p.stock > 0'; }
    if (!empty($filters['q'])){ $where .= ' AND (p.name LIKE ? OR p.description LIKE ?)'; $params[] = '%'.$filters['q'].'%'; $params[] = '%'.$filters['q'].'%'; }

    // total
    $countSql = 'SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id' . $where;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = max(0, ($page - 1) * $perPage);
    $sql = 'SELECT p.*, c.name as category FROM products p JOIN categories c ON p.category_id = c.id' . $where . ' ORDER BY p.id DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $execParams = array_merge($params, [$perPage, $offset]);
    $stmt->execute($execParams);
    $data = $stmt->fetchAll();
    return ['data' => $data, 'total' => $total];
} 

function getProduct($id){ global $pdo; $stmt = $pdo->prepare('SELECT p.*, c.name as category FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?'); $stmt->execute([$id]); return $stmt->fetch(); }

function createProduct($data){ global $pdo; $stmt = $pdo->prepare('INSERT INTO products (category_id, name, description, price, stock, image, featured) VALUES (?,?,?,?,?,?,?)'); $stmt->execute([$data['category_id'],$data['name'],$data['description'],$data['price'],$data['stock'],$data['image']??null,$data['featured']??0]); return $pdo->lastInsertId(); }

function updateProduct($id, $data){ global $pdo; $stmt = $pdo->prepare('UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?, featured=? WHERE id=?'); return $stmt->execute([$data['category_id'],$data['name'],$data['description'],$data['price'],$data['stock'],$data['image']??null,$data['featured']??0, $id]); }

function deleteProduct($id){ global $pdo; $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?'); return $stmt->execute([$id]); }

// Pedidos
function getCouponByCode($code){ global $pdo; $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND active = 1 LIMIT 1'); $stmt->execute([$code]); $c = $stmt->fetch(); if (!$c) return null; if ($c['expires_at'] && strtotime($c['expires_at']) < time()) return null; return $c; }

function calculateCouponDiscount($coupon, $total){ if (!$coupon) return ['valid'=>false]; if ($coupon['type'] === 'percent'){ $discount = ($total * floatval($coupon['value'])) / 100.0; } else { $discount = floatval($coupon['value']); } $discount = min($discount, $total); return ['valid'=>true,'discount'=>round($discount,2),'new_total'=>round($total - $discount,2)]; }

function createOrder($userId, $items, $providedTotal = null, $couponCode = null){
    global $pdo;
    try{
        $pdo->beginTransaction();
        // Calcular total real desde DB
        $total = 0;
        $priceStmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
        foreach($items as $it){
            $priceStmt->execute([$it['product_id']]);
            $price = $priceStmt->fetchColumn();
            if ($price === false) throw new Exception('Producto no encontrado: '.$it['product_id']);
            $total += $price * $it['quantity'];
        }

        $appliedCoupon = null; $discount = 0.0;
        if ($couponCode){
            $coupon = getCouponByCode($couponCode);
            if (!$coupon) throw new Exception('Cupón inválido o expirado');
            $calc = calculateCouponDiscount($coupon, $total);
            if (!$calc['valid']) throw new Exception('Cupón inválido');
            $discount = $calc['discount'];
            $total = $calc['new_total'];
            $appliedCoupon = $coupon['code'];
        }

        $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, status, coupon_code) VALUES (?,?,?,?)');
        $stmt->execute([$userId, $total, 'pending', $appliedCoupon]);
        $orderId = $pdo->lastInsertId();
        $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)');
        $stmtUpdateStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        foreach($items as $it){
            // obtener precio real
            $priceStmt->execute([$it['product_id']]);
            $price = $priceStmt->fetchColumn();
            $stmtItem->execute([$orderId, $it['product_id'], $it['quantity'], $price]);
            $ok = $stmtUpdateStock->execute([$it['quantity'], $it['product_id'], $it['quantity']]);
            if (!$ok || $stmtUpdateStock->rowCount() === 0){ throw new Exception('Stock insuficiente para el producto '.$it['product_id']); }
        }
        $pdo->commit();
        return $orderId;
    }catch(Exception $e){ $pdo->rollBack(); return ['error'=>$e->getMessage()]; }
}

function getOrder($orderId){ global $pdo; $stmt = $pdo->prepare('SELECT o.*, u.name as customer_name, u.email, inv.invoice_number FROM orders o JOIN users u ON u.id = o.user_id LEFT JOIN invoices inv ON inv.order_id = o.id WHERE o.id = ?'); $stmt->execute([$orderId]); $order = $stmt->fetch(); if (!$order) return null; $stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?'); $stmt->execute([$orderId]); $order['items'] = $stmt->fetchAll(); return $order; }

function getUserOrders($userId){ global $pdo; $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC'); $stmt->execute([$userId]); return $stmt->fetchAll(); }

function updateOrderStatus($orderId, $status){ global $pdo; $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?'); return $stmt->execute([$status, $orderId]); }

// Facturación
function getNextInvoiceNumber(){ global $pdo; $year = date('Y');
    $stmt = $pdo->prepare("SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(["INV-{$year}-%"]);
    $last = $stmt->fetchColumn();
    if (!$last){ return sprintf('INV-%s-0001', $year); }
    $parts = explode('-', $last);
    $seq = (int)end($parts) + 1;
    return sprintf('INV-%s-%04d', $year, $seq);
}

function saveInvoiceRecord($orderId, $invoiceNumber, $filePath){ global $pdo; $stmt = $pdo->prepare('INSERT INTO invoices (order_id, invoice_number, file_path) VALUES (?,?,?)'); $stmt->execute([$orderId, $invoiceNumber, $filePath]); return $pdo->lastInsertId(); }

// Reports - sum of sales
function salesReport($startDate, $endDate){ global $pdo; $stmt = $pdo->prepare('SELECT DATE(o.created_at) as date, SUM(o.total) as total, COUNT(o.id) as orders FROM orders o WHERE o.created_at BETWEEN ? AND ? GROUP BY DATE(o.created_at) ORDER BY DATE(o.created_at)'); $stmt->execute([$startDate, $endDate]); return $stmt->fetchAll(); }

// Obtener todos los pedidos (admin) con paginación y filtros básicos
function getAllOrders($page = 1, $perPage = 20, $filters = []){
    global $pdo;
    $where = ' WHERE 1=1';
    $params = [];
    if (!empty($filters['q'])){
        $q = $filters['q'];
        if (ctype_digit($q)){
            $where .= ' AND (o.id = ? OR u.name LIKE ? OR u.email LIKE ?)';
            $params[] = intval($q);
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
        } else {
            $where .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
        }
    }
    if (!empty($filters['status'])){ $where .= ' AND o.status = ?'; $params[] = $filters['status']; }
    if (!empty($filters['start'])){ $where .= ' AND o.created_at >= ?'; $params[] = $filters['start'] . ' 00:00:00'; }
    if (!empty($filters['end'])){ $where .= ' AND o.created_at <= ?'; $params[] = $filters['end'] . ' 23:59:59'; }

    // total
    $countSql = 'SELECT COUNT(*) FROM orders o JOIN users u ON u.id = o.user_id' . $where;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = max(0, ($page - 1) * $perPage);
    $sql = 'SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON u.id = o.user_id' . $where . ' ORDER BY o.created_at DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $execParams = array_merge($params, [$perPage, $offset]);
    $stmt->execute($execParams);
    $data = $stmt->fetchAll();
    return ['data' => $data, 'total' => $total];
}
//hola
?>