<?php
// Panel Admin – Supermercado Yaruquíes
// Fase inicial (auth avanzada en siguiente fase)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin | Supermercado Yaruquíes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="/Supermercado/frontend/public/css/admin.css">
</head>
<body class="bg-light">

<!-- TOPBAR -->
<nav class="navbar admin-topbar px-4">
  <span class="navbar-brand mb-0 h1">
    🛒 Supermercado Yaruquíes
  </span>

  <div class="d-flex align-items-center gap-3">
    <span class="badge bg-success">Admin</span>
    <a href="/Supermercado/index.php" class="btn btn-sm btn-light">
      Salir
    </a>
  </div>
</nav>

<!-- CONTENIDO -->
<div class="container-main container">

  <!-- ENCABEZADO -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="fw-bold mb-1">Panel de Administración</h1>
      <p class="small-muted mb-0">
        Control y gestión del sistema
      </p>
    </div>
    <span class="badge bg-primary">
      Sistema activo
    </span>
  </div>

  <!-- KPIs -->
  <div class="row g-4 mb-4">

    <div class="col-md-3">
      <div class="kpi text-center">
        <h2>📦</h2>
        <h6 class="fw-bold mb-0">Productos</h6>
        <small class="small-muted">Inventario</small>
      </div>
    </div>

    <div class="col-md-3">
      <div class="kpi text-center">
        <h2>🧾</h2>
        <h6 class="fw-bold mb-0">Pedidos</h6>
        <small class="small-muted">Ventas</small>
      </div>
    </div>

    <div class="col-md-3">
      <div class="kpi text-center">
        <h2>🏷️</h2>
        <h6 class="fw-bold mb-0">Cupones</h6>
        <small class="small-muted">Promociones</small>
      </div>
    </div>

    <div class="col-md-3">
      <div class="kpi text-center">
        <h2>📊</h2>
        <h6 class="fw-bold mb-0">Reportes</h6>
        <small class="small-muted">Métricas</small>
      </div>
    </div>

  </div>

  <!-- ACCESOS -->
  <div class="row g-4">

    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-body text-center">
          <h5>📦 Productos</h5>
          <p class="small-muted">Gestión de inventario</p>
          <a href="products.php" class="btn btn-primary btn-sm w-100">
            Administrar
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-body text-center">
          <h5>🧾 Pedidos</h5>
          <p class="small-muted">Control de ventas</p>
          <a href="orders.php" class="btn btn-outline-primary btn-sm w-100">
            Ver pedidos
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-body text-center">
          <h5>🏷️ Cupones</h5>
          <p class="small-muted">Descuentos</p>
          <a href="coupons.php" class="btn btn-outline-primary btn-sm w-100">
            Gestionar
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-body text-center">
          <h5>📊 Reportes</h5>
          <p class="small-muted">Estadísticas</p>
          <a href="reports.php" class="btn btn-outline-primary btn-sm w-100">
            Ver reportes
          </a>
        </div>
      </div>
    </div>

  </div>

  <!-- ESTADO -->
  <div class="card mt-5">
    <div class="card-header fw-bold">
      Estado del sistema
    </div>
    <div class="card-body">
      <ul class="mb-0">
        <li>✅ Backend conectado</li>
        <li>✅ API operativa</li>
        <li>⏳ Seguridad admin avanzada</li>
        <li>⏳ Dashboard con gráficas</li>
      </ul>
    </div>
  </div>

</div>


<!-- Bootstrap JS -->
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
