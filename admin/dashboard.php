<?php
// Dashboard básico con lugar para métricas
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
<?php $title = 'Admin Dashboard'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container container-main mt-4">
  <h1>Dashboard</h1>
  <div class="row">
    <div class="col-md-6">
      <canvas id="salesChart"></canvas>
    </div>
    <div class="col-md-6">
      <h3>Métricas</h3>
      <ul>
        <li>Ventas totales: <strong>$0.00</strong></li>
        <li>Productos más vendidos: <strong>-</strong></li>
      </ul>
    </div>
  </div>
</div>
<script>
const ctx = document.getElementById('salesChart');
if (ctx) new Chart(ctx, { type: 'line', data: { labels: ['Ene','Feb','Mar'], datasets:[{label:'Ventas',data:[0,0,0],borderColor:'#007bff'}] } });
</script>
</body>
</html>