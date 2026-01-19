<?php
// admin/dashboard.php
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['user']['name'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Supermercado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="/Supermercado/frontend/public/css/admin.css">

  <style>
    body { overflow-x: hidden; }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      min-height: 100vh;
      background: linear-gradient(180deg, #0f172a, #1e293b);
      color: #fff;
    }

    .sidebar h4 {
      letter-spacing: .5px;
    }

    .sidebar a {
      color: #cbd5f5;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 18px;
      border-radius: 8px;
      font-size: 0.95rem;
      transition: all .2s ease;
    }

    .sidebar a:hover {
      background: rgba(255,255,255,.08);
      color: #fff;
      transform: translateX(4px);
    }

    .sidebar a.active {
      background: rgba(255,255,255,.15);
      color: #fff;
      font-weight: 600;
    }

    /* CONTENT */
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

    /* STAT CARDS */
    .stat-card {
      border: none;
      border-radius: 14px;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 25px rgba(0,0,0,.08);
    }

    .stat-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      color: #fff;
    }

    .bg-products { background: #0d6efd; }
    .bg-orders   { background: #198754; }
    .bg-sales    { background: #ffc107; color:#000; }
    .bg-users    { background: #dc3545; }

    /* QUICK ACCESS */
    .quick-card {
      border-radius: 14px;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .quick-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 25px rgba(0,0,0,.08);
    }
  </style>
</head>

<body class="bg-light">

<!-- SIDEBAR -->
<div class="sidebar position-fixed p-4">
  <h4 class="fw-bold mb-4">🛒 Supermercado</h4>

  <a href="dashboard.php" class="active">📊 Dashboard</a>
  <a href="products.php">📦 Productos</a>
  <a href="orders.php">🧾 Pedidos</a>
  <a href="coupons.php">🎟 Cupones</a>
  <a href="reports.php">📈 Reportes</a>

  <hr class="text-secondary my-4">

  <a href="logout.php" class="text-danger fw-semibold">🚪 Cerrar sesión</a>
</div>

<!-- CONTENT -->
<div class="content">

  <!-- TOPBAR -->
  <nav class="navbar bg-white shadow-sm px-4 topbar">
    <span class="navbar-text">
      Bienvenido, <strong><?= htmlspecialchars($adminName) ?></strong>
    </span>
  </nav>

  <div class="container-fluid px-4 mt-4">

    <!-- TITLE -->
    <h2 class="fw-bold mb-1">Dashboard</h2>
    <p class="text-muted mb-4">Resumen general del sistema</p>

    <!-- STATS -->
    <div class="row g-4 mb-4">

      <div class="col-md-3">
        <div class="card stat-card p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-products">📦</div>
            <div>
              <small class="text-muted">Productos</small>
              <h4 class="fw-bold mb-0">—</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-orders">🧾</div>
            <div>
              <small class="text-muted">Pedidos</small>
              <h4 class="fw-bold mb-0">—</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-sales">💰</div>
            <div>
              <small class="text-muted">Ventas</small>
              <h4 class="fw-bold mb-0">$ —</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-users">👥</div>
            <div>
              <small class="text-muted">Usuarios</small>
              <h4 class="fw-bold mb-0">—</h4>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- QUICK ACCESS -->
    <div class="row g-4">

      <div class="col-md-4">
        <div class="card quick-card p-3">
          <h5>📦 Gestión de productos</h5>
          <p class="text-muted small">
            Crear, editar y administrar el inventario.
          </p>
          <a href="products.php" class="btn btn-primary btn-sm">
            Ir a productos →
          </a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card quick-card p-3">
          <h5>🧾 Pedidos</h5>
          <p class="text-muted small">
            Control y seguimiento de pedidos.
          </p>
          <a href="orders.php" class="btn btn-success btn-sm">
            Ver pedidos →
          </a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card quick-card p-3">
          <h5>📈 Reportes</h5>
          <p class="text-muted small">
            Estadísticas, ventas y exportaciones.
          </p>
          <a href="reports.php" class="btn btn-warning btn-sm">
            Ver reportes →
          </a>
        </div>
      </div>

    </div>

  </div>
</div>

</body>
</html>
