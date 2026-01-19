<?php
// Admin – Gestión de Productos
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
  <title>Admin | Productos</title>
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

    /* TABLE */
    .table-card {
      border-radius: 14px;
      overflow: hidden;
    }

    .table thead {
      background: #f8fafc;
    }

    tr.selected {
      background: #eef2ff !important;
    }

    /* ACTION BUTTONS */
    .btn-icon {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* MODAL */
    .modal-content {
      border-radius: 14px;
    }
  </style>
</head>

<body class="bg-light">

<!-- SIDEBAR -->
<div class="sidebar position-fixed p-4">
  <h4 class="fw-bold mb-4">🛒 Supermercado</h4>

  <a href="dashboard.php">📊 Dashboard</a>
  <a href="products.php" class="active">📦 Productos</a>
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
    <span class="navbar-text fw-semibold">
      Gestión de productos
    </span>
  </nav>

  <div class="container-fluid px-4 mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 class="fw-bold mb-0">Productos</h2>
        <p class="text-muted small mb-0">
          Administración del inventario
        </p>
      </div>

      <button id="newBtn" class="btn btn-success btn-icon">
        ➕ Nuevo producto
      </button>
    </div>

    <!-- ACTIONS -->
    <div class="d-flex flex-wrap gap-2 mb-3">
      <button class="btn btn-outline-success btn-sm">
        📤 Exportar seleccionados
      </button>

      <button class="btn btn-outline-secondary btn-sm">
        📤 Exportar filtrados
      </button>

      <button id="exportXmlBtn" class="btn btn-outline-success btn-sm">
        📤 Exportar XML
      </button>

      <label class="btn btn-outline-primary btn-sm mb-0">
        📥 Importar XML
        <input type="file" id="importXmlInput" accept=".xml" hidden>
      </label>
    </div>

    <!-- TABLE -->
    <div class="card table-card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table">
          <thead>
            <tr>
              <th width="40">
                <input type="checkbox" id="selectAllProducts">
              </th>
              <th>ID</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <!-- PAGINATION -->
    <nav class="mt-3">
      <ul id="prodPagination" class="pagination"></ul>
    </nav>

  </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 id="formTitle" class="modal-title">Producto</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="pid">

        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input id="pname" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Precio</label>
          <input id="pprice" type="number" step="0.01" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Stock</label>
          <input id="pstock" type="number" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Categoría</label>
          <select id="pcat" class="form-select">
            <option value="1">General</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button id="saveBtn" class="btn btn-primary">
          Guardar
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
async function api(action, opts = {}) {
  const headers = { 'Content-Type': 'application/json' };
  const csrf = localStorage.getItem('admin_csrf');
  if (csrf) headers['X-CSRF-Token'] = csrf;

  const qs = opts.query ? '&' + opts.query : '';
  const res = await fetch(
    '/Supermercado/backend/public/api.php?action=' + action + qs,
    { method: opts.method || 'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined }
  );
  return await res.json();
}

async function loadProducts() {
  const json = await api('products');
  const tbody = document.querySelector('#table tbody');
  tbody.innerHTML = '';

  if (!json.products || json.products.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Sin productos</td></tr>`;
    return;
  }

  json.products.forEach(p => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="checkbox" class="prodCheck"></td>
      <td>${p.id}</td>
      <td>${p.name}</td>
      <td>${p.category}</td>
      <td>$${p.price}</td>
      <td>${p.stock}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-primary edit" data-id="${p.id}">Editar</button>
        <button class="btn btn-sm btn-danger del" data-id="${p.id}">Eliminar</button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  document.querySelectorAll('.edit').forEach(b =>
    b.addEventListener('click', e => openEdit(e.target.dataset.id))
  );

  document.querySelectorAll('.del').forEach(b =>
    b.addEventListener('click', e => delProduct(e.target.dataset.id))
  );
}

function openNew() {
  pid.value = '';
  pname.value = '';
  pprice.value = '';
  pstock.value = '';
  formTitle.innerText = 'Nuevo producto';
  new bootstrap.Modal(formModal).show();
}

async function openEdit(id) {
  const json = await api('product', { query: 'id=' + id });
  const p = json.product;

  pid.value = p.id;
  pname.value = p.name;
  pprice.value = p.price;
  pstock.value = p.stock;
  formTitle.innerText = 'Editar producto';
  new bootstrap.Modal(formModal).show();
}

async function save() {
  const body = {
    name: pname.value,
    price: pprice.value,
    stock: pstock.value,
    category_id: pcat.value
  };

  if (pid.value) {
    body.id = pid.value;
    await api('product_update', { method: 'POST', body });
  } else {
    await api('product_create', { method: 'POST', body });
  }

  bootstrap.Modal.getInstance(formModal).hide();
  loadProducts();
}

async function delProduct(id) {
  if (!confirm('¿Eliminar producto?')) return;
  await api('product_delete', { method: 'POST', query: 'id=' + id });
  loadProducts();
}

newBtn.addEventListener('click', openNew);
saveBtn.addEventListener('click', save);

document.getElementById('exportXmlBtn').addEventListener('click', () => {
  window.location.href =
    '/Supermercado/backend/public/api.php?action=products_export_xml';
});

document.getElementById('importXmlInput').addEventListener('change', async e => {
  const file = e.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('xml', file);

  const res = await fetch(
    '/Supermercado/backend/public/api.php?action=products_import_xml',
    {
      method: 'POST',
      headers: { 'X-CSRF-Token': localStorage.getItem('admin_csrf') },
      body: formData
    }
  );

  const json = await res.json();
  alert(json.success ? `Importados: ${json.imported}` : json.error);
  loadProducts();
});

loadProducts();
</script>

</body>
</html>
