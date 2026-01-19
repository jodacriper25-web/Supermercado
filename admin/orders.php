<?php
// Admin – Pedidos (PRO)
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
  <title>Admin | Pedidos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="/Supermercado/frontend/public/css/admin.css">

  <style>
    body{overflow-x:hidden}

    /* SIDEBAR */
    .sidebar{
      width:260px;
      min-height:100vh;
      background:linear-gradient(180deg,#0f172a,#1e293b);
      color:#fff;
    }

    .sidebar a{
      color:#cbd5f5;
      text-decoration:none;
      display:flex;
      align-items:center;
      gap:10px;
      padding:12px 18px;
      border-radius:8px;
      transition:.2s;
    }

    .sidebar a:hover{
      background:rgba(255,255,255,.08);
      transform:translateX(4px);
      color:#fff;
    }

    .sidebar a.active{
      background:rgba(255,255,255,.15);
      font-weight:600;
      color:#fff;
    }

    .content{
      margin-left:260px;
      min-height:100vh;
    }

    .badge-status{
      font-size:.75rem;
      padding:.35em .6em;
    }
  </style>
</head>

<body class="bg-light">

<!-- SIDEBAR -->
<div class="sidebar position-fixed p-4">
  <h4 class="fw-bold mb-4">🛒 Supermercado</h4>

  <a href="dashboard.php">📊 Dashboard</a>
  <a href="products.php">📦 Productos</a>
  <a href="orders.php" class="active">🧾 Pedidos</a>
  <a href="coupons.php">🏷️ Cupones</a>
  <a href="reports.php">📈 Reportes</a>

  <hr class="text-secondary my-4">

  <a href="logout.php" class="text-danger fw-semibold">🚪 Cerrar sesión</a>
</div>

<!-- CONTENT -->
<div class="content">

  <!-- TOPBAR -->
  <nav class="navbar bg-white shadow-sm px-4" style="height:64px">
    <span class="navbar-text fw-semibold">
      Gestión y seguimiento de pedidos
    </span>
  </nav>

  <div class="container-fluid px-4 mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 class="fw-bold mb-0">Pedidos</h2>
        <p class="text-muted small mb-0">
          Control completo del flujo de ventas
        </p>
      </div>
      <div id="ordersCount" class="text-muted small"></div>
    </div>

    <!-- FILTROS -->
    <div class="card shadow-sm mb-3">
      <div class="card-body d-flex flex-wrap gap-2 align-items-center">

        <input id="ordersSearch" class="form-control" placeholder="Buscar por ID, cliente o email" style="max-width:320px">

        <select id="ordersStatus" class="form-select" style="max-width:200px">
          <option value="">Todos los estados</option>
          <option value="pending">Pendiente</option>
          <option value="preparing">En preparación</option>
          <option value="sent">Enviado</option>
          <option value="delivered">Entregado</option>
        </select>

        <input id="ordersStart" type="date" class="form-control" style="max-width:160px">
        <input id="ordersEnd" type="date" class="form-control" style="max-width:160px">

        <button id="ordersLoadBtn" class="btn btn-primary">
          Filtrar
        </button>

        <button id="ordersClearBtn" class="btn btn-outline-secondary">
          Limpiar
        </button>

        <button id="exportSelectedBtn" class="btn btn-outline-success">
          Exportar seleccionados
        </button>

        <button id="exportFilteredBtn" class="btn btn-outline-success">
          Exportar filtrados
        </button>

        <div id="ordersSpinner" class="spinner-border text-primary ms-2" style="display:none" role="status"></div>
      </div>
    </div>

    <!-- TABLA -->
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table">
          <thead class="table-light">
            <tr>
              <th width="40">
                <input type="checkbox" id="selectAllOrders">
              </th>
              <th>ID</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                Cargando pedidos...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PAGINACIÓN -->
    <nav class="mt-3">
      <ul id="ordersPagination" class="pagination pagination-sm"></ul>
    </nav>

  </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">Detalle del pedido</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="orderBody">
        <!-- contenido dinámico -->
      </div>

      <div class="modal-footer">
        <select id="statusSelect" class="form-select me-2" style="max-width:220px">
          <option value="pending">Pendiente</option>
          <option value="preparing">En preparación</option>
          <option value="sent">Enviado</option>
          <option value="delivered">Entregado</option>
        </select>

        <button id="updateStatusBtn" class="btn btn-primary">
          Actualizar estado
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- =========================
     JS ORIGINAL (NO TOCADO)
========================= -->
<script>
/*
  👉 AQUÍ VA EXACTAMENTE TU JS ORIGINAL
  👉 NO lo modifiqué para no romper nada
  👉 Solo pega tu script de pedidos debajo
*/
</script>

</body>
</html>
