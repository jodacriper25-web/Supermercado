<?php
// Admin – Reportes
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin | Reportes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="/Supermercado/frontend/public/css/admin.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body { overflow-x: hidden; }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      min-height: 100vh;
      background: linear-gradient(180deg, #0f172a, #1e293b);
      color: #fff;
    }

    .sidebar a {
      color: #cbd5f5;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 18px;
      border-radius: 8px;
      transition: all .2s ease;
    }

    .sidebar a:hover {
      background: rgba(255,255,255,.08);
      transform: translateX(4px);
      color: #fff;
    }

    .sidebar a.active {
      background: rgba(255,255,255,.15);
      font-weight: 600;
      color: #fff;
    }

    .content {
      margin-left: 260px;
      min-height: 100vh;
    }

    /* TOPBAR */
    .topbar {
      height: 64px;
      display: flex;
      align-items: center;
    }

    /* CHART CARD */
    .chart-card {
      border-radius: 14px;
    }

    .filters input {
      max-width: 180px;
    }
  </style>
</head>

<body class="bg-light">

<!-- SIDEBAR -->
<div class="sidebar position-fixed p-4">
  <h4 class="fw-bold mb-4">🛒 Supermercado</h4>

  <a href="dashboard.php">📊 Dashboard</a>
  <a href="products.php">📦 Productos</a>
  <a href="orders.php">🧾 Pedidos</a>
  <a href="coupons.php">🎟 Cupones</a>
  <a href="reports.php" class="active">📈 Reportes</a>

  <hr class="text-secondary my-4">

  <a href="logout.php" class="text-danger fw-semibold">🚪 Cerrar sesión</a>
</div>

<!-- CONTENT -->
<div class="content">

  <!-- TOPBAR -->
  <nav class="navbar bg-white shadow-sm px-4 topbar">
    <span class="navbar-text fw-semibold">
      Reportes y estadísticas
    </span>
  </nav>

  <div class="container-fluid px-4 mt-4">

    <!-- HEADER -->
    <div class="mb-3">
      <h2 class="fw-bold mb-0">Reportes de ventas</h2>
      <p class="text-muted small mb-0">
        Análisis visual y exportación de resultados
      </p>
    </div>

    <!-- FILTERS -->
    <div class="card shadow-sm mb-3">
      <div class="card-body d-flex flex-wrap align-items-end gap-2 filters">
        <div>
          <label class="form-label small">Desde</label>
          <input id="start" type="date" class="form-control">
        </div>

        <div>
          <label class="form-label small">Hasta</label>
          <input id="end" type="date" class="form-control">
        </div>

        <button id="loadBtn" class="btn btn-primary">
          📊 Generar reporte
        </button>

        <a id="csvLink" class="btn btn-outline-secondary ms-auto" target="_blank">
          📥 CSV
        </a>

        <a id="pdfLink" class="btn btn-outline-secondary" target="_blank">
          📄 PDF
        </a>
      </div>
    </div>

    <!-- CHART -->
    <div class="card chart-card shadow-sm p-3">
      <canvas id="chart" height="90"></canvas>
    </div>

  </div>
</div>

<script>
let chartInstance = null;

document.getElementById('loadBtn').addEventListener('click', async () => {
  const startDate = start.value;
  const endDate = end.value;

  if (!startDate || !endDate) {
    alert('Selecciona un rango de fechas');
    return;
  }

  csvLink.href =
    `/Supermercado/backend/public/api.php?action=reports&start=${startDate}&end=${endDate}&format=csv`;

  pdfLink.href =
    `/Supermercado/backend/public/api.php?action=reports&start=${startDate}&end=${endDate}&format=pdf`;

  const res = await fetch(
    `/Supermercado/backend/public/api.php?action=reports&start=${startDate}&end=${endDate}`
  );

  const json = await res.json();

  const labels = json.report.map(r => r.date);
  const data = json.report.map(r => parseFloat(r.total));

  if (chartInstance) chartInstance.destroy();

  chartInstance = new Chart(chart, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Ventas ($)',
        data,
        tension: 0.35,
        fill: true,
        borderWidth: 2,
        backgroundColor: 'rgba(13,110,253,.15)',
        borderColor: '#0d6efd',
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `$ ${ctx.parsed.y.toFixed(2)}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: v => '$ ' + v
          }
        }
      }
    }
  });
});
</script>

</body>
</html>
