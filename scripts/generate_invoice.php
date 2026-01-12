<?php
// Wrapper CLI para generar factura usando backend src
require_once __DIR__ . '/../backend/src/db.php';
require_once __DIR__ . '/../backend/src/invoice.php';

// Uso: php generate_invoice.php <orderId> <invoiceNumber>
if (php_sapi_name() === 'cli'){
    $orderId = $argv[1] ?? null;
    $invoiceNumber = $argv[2] ?? null;
    if ($orderId && $invoiceNumber) {
        $path = generateInvoicePdf($orderId, $invoiceNumber);
        echo $path ? "Generado: $path\n" : "Error\n";
    } else {
        echo "Uso: php generate_invoice.php <orderId> <invoiceNumber>\n";
    }
}
