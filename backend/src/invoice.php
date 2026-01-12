<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models.php';
// Cargar autoload para TCPDF si está disponible
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

use TCPDF;

function generateInvoicePdf($orderId, $invoiceNumber){
    global $pdo;
    $stmt = $pdo->prepare('SELECT o.*, u.name as customer_name, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) return false;
    $stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();

    $pdf = new TCPDF();
    $pdf->SetCreator('Supermercado Yaruquíes');
    $pdf->AddPage();
    $html = "<h1>Factura {$invoiceNumber}</h1>";
    $html .= "<p>Cliente: {$order['customer_name']} ({$order['email']})</p>";
    $html .= "<table border=1 cellpadding=4><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr></thead><tbody>";
    foreach($items as $it){ $html .= "<tr><td>{$it['name']}</td><td>{$it['quantity']}</td><td>{$it['price']}</td><td>".($it['quantity']*$it['price'])."</td></tr>"; }
    $html .= "</tbody></table>";
    $html .= "<p><strong>Total: " . number_format($order['total'],2) . "</strong></p>";

    $pdf->writeHTML($html);

    $dir = __DIR__ . '/../../invoices';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $path = $dir . "/{$invoiceNumber}.pdf";
    $pdf->Output($path, 'F');

    saveInvoiceRecord($orderId, $invoiceNumber, $path);

    return $path;
}
?>