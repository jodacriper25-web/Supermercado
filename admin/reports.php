<?php
// Reportes Admin
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin | Reportes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<nav class="navbar admin-topbar">
  <div class="container-fluid">
    <span class="navbar-brand">📊 Reportes de Ventas</span>
    <a href="dashboard.php" class="btn btn-sm btn-outline-light">Dashboard</a>
  </div>
</nav>

<div class="container container-main">
  <div class="d-flex gap-2 mb-3">
    <input id="start" type="date" class="form-control" style="max-width:180px">
    <input id="end" type="date" class="form-control" style="max-width:180px">
    <button id="loadBtn" class="btn btn-primary">Cargar</button>
    <a id="csvLink" class="btn btn-outline-secondary">CSV</a>
    <a id="pdfLink" class="btn btn-outline-secondary">PDF</a>
  </div>

  <div class="card shadow-sm p-3">
    <canvas id="chart"></canvas>
  </div>
</div>

<script>
document.getElementById('loadBtn').addEventListener('click', async ()=>{
  const start = start.value;
  const end = end.value;

  csvLink.href = `/Supermercado/backend/public/api.php?action=reports&start=${start}&end=${end}&format=csv`;
  pdfLink.href = `/Supermercado/backend/public/api.php?action=reports&start=${start}&end=${end}&format=pdf`;

  const res = await fetch(`/Supermercado/backend/public/api.php?action=reports&start=${start}&end=${end}`);
  const json = await res.json();

  const labels = json.report.map(r=>r.date);
  const data = json.report.map(r=>parseFloat(r.total));

  if (window.chart) window.chart.destroy();
  window.chart = new Chart(chart, {
    type:'line',
    data:{ labels, datasets:[{label:'Ventas', data}] }
  });
});
</script>

</body>
</html>
