<?php
// Pruebas básicas de endpoints (CLI)
function get($url){ $opts = ['http'=>['method'=>'GET','timeout'=>5]]; $context = stream_context_create($opts); return @file_get_contents($url, false, $context); }

$base = 'http://localhost/Supermercado/backend/public/api.php';

echo "Comprobando endpoint products (paginado)...\n";
$res = get($base . '?action=products&page=1&per_page=2');
if (!$res){ echo "Error: no se pudo contactar al API. Asegúrate de que Apache y XAMPP estén en ejecución.\n"; exit(1); }
$json = json_decode($res, true);
if (!isset($json['products'])){ echo "Error: respuesta inesperada para products\n"; exit(1); }
echo "OK - products devuelve " . count($json['products']) . " ítems (total: " . ($json['total'] ?? 'n/a') . ")\n";

echo "Comprobando endpoint product (detalle)...\n";
if (isset($json['products'][0]['id'])){
  $id = $json['products'][0]['id'];
  $res2 = get($base . '?action=product&id=' . $id);
  $j2 = json_decode($res2, true);
  if (!isset($j2['product'])){ echo "Error: product detail inesperado\n"; } else { echo "OK - product #$id -> " . ($j2['product']['name'] ?? 'n/a') . "\n"; }
}

echo "Comprobando reports CSV (sin auth, debe devolver 403 o error si requiere admin)...\n";
$res3 = get($base . '?action=reports&start=2020-01-01&end=2030-01-01&format=csv');
if ($res3 === false) echo "No response for reports (check server)\n"; else echo "reports response length: " . strlen($res3) . " bytes\n";

echo "Pruebas completadas.\n";
