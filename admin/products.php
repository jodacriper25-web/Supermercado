<?php
// Admin – Gestión de Productos
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
</head>

<body class="bg-light">

<!-- TOPBAR -->
<nav class="navbar admin-topbar px-4">
  <span class="navbar-brand fw-bold">
    🛒 Admin · Productos
  </span>

  <div class="d-flex gap-2">
    <a href="/Supermercado/admin/index.php" class="btn btn-sm btn-light">
      Dashboard
    </a>
  </div>
</nav>

<!-- CONTENIDO -->
<div class="container container-main">

  <!-- ENCABEZADO -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="fw-bold mb-0">Productos</h1>
      <p class="small-muted">Administración del inventario</p>
    </div>
    <button id="newBtn" class="btn btn-success">
      ➕ Nuevo producto
    </button>
  </div>

  <!-- ACCIONES -->
  <div class="d-flex gap-2 mb-3">
    <button id="exportProductsSelectedBtn" class="btn btn-outline-success btn-sm">
      Exportar seleccionados
    </button>
    <button id="exportProductsFilteredBtn" class="btn btn-outline-secondary btn-sm">
      Exportar filtrados
    </button>
  </div>

  <!-- TABLA -->
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="table">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAllProducts"></th>
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

  <!-- PAGINACIÓN -->
  <nav class="mt-3">
    <ul id="prodPagination" class="pagination"></ul>
  </nav>

</div>

<!-- MODAL FORM -->
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
/* ======================
   API HELPER
====================== */
async function api(action, opts = {}) {
  const headers = opts.headers || {};
  headers['Content-Type'] = 'application/json';
  const csrf = localStorage.getItem('admin_csrf') || '';
  if (csrf) headers['X-CSRF-Token'] = csrf;

  const qs = opts.query ? '&' + opts.query : '';
  const res = await fetch(
    '/Supermercado/backend/public/api.php?action=' + action + qs,
    { method: opts.method || 'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined }
  );
  return await res.json();
}

/* ======================
   LISTADO
====================== */
let currentPage = 1;
const perPage = 10;

async function loadProducts(page = 1) {
  currentPage = page;
  const json = await api('products', { query: `page=${page}&per_page=${perPage}` });
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

/* ======================
   CRUD
====================== */
function openNew() {
  pid.value = '';
  pname.value = '';
  pprice.value = '';
  pstock.value = '';
  formTitle.innerText = 'Nuevo producto';
  new bootstrap.Modal(formModal).show();
}

async function openEdit(id) {
  const res = await fetch('/Supermercado/backend/public/api.php?action=product&id=' + id);
  const p = (await res.json()).product;

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
  loadProducts(currentPage);
}

async function delProduct(id) {
  if (!confirm('¿Eliminar producto?')) return;
  await api('product_delete', { method: 'POST', query: 'id=' + id });
  loadProducts(currentPage);
}

/* ======================
   EVENTS
====================== */
newBtn.addEventListener('click', openNew);
saveBtn.addEventListener('click', save);

loadProducts();
</script>

</body>
</html>
