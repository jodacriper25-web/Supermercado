<?php
// Admin – Gestión de cupones
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin | Cupones</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin styles -->
  <link rel="stylesheet" href="admin.css">
</head>
<body class="bg-light">

<!-- TOPBAR -->
<nav class="navbar admin-topbar">
  <div class="container-fluid">
    <span class="navbar-brand">🏷️ Cupones</span>
    <a href="dashboard.php" class="btn btn-sm btn-light">Dashboard</a>
  </div>
</nav>

<div class="container container-main">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="fw-bold mb-0">Gestión de cupones</h1>
      <p class="small-muted mb-0">Descuentos y promociones</p>
    </div>
  </div>

  <!-- ACTIONS -->
  <div class="mb-3 d-flex flex-wrap gap-2">
    <button id="newBtn" class="btn btn-success">Nuevo cupón</button>
    <button id="exportCouponsSelectedBtn" class="btn btn-outline-success">Exportar seleccionados</button>
    <button id="exportCouponsFilteredBtn" class="btn btn-outline-success">Exportar todos</button>
  </div>

  <!-- TABLE -->
  <div class="table-responsive shadow-sm">
    <table class="table table-hover mb-0" id="table">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" id="selectAllCoupons"></th>
          <th>ID</th>
          <th>Código</th>
          <th>Tipo</th>
          <th>Valor</th>
          <th>Activo</th>
          <th>Expira</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

</div>

<!-- MODAL FORM -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="formTitle" class="modal-title">Nuevo cupón</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="cid">

        <div class="mb-2">
          <label class="form-label">Código</label>
          <input id="ccode" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Tipo</label>
          <select id="ctype" class="form-select">
            <option value="percent">Porcentaje (%)</option>
            <option value="fixed">Valor fijo ($)</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Valor</label>
          <input id="cvalue" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Expira</label>
          <input id="cexpires" type="datetime-local" class="form-control">
        </div>

        <div class="form-check">
          <input id="cactive" class="form-check-input" type="checkbox" checked>
          <label class="form-check-label">Activo</label>
        </div>
      </div>

      <div class="modal-footer">
        <button id="saveBtn" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- =======================
     LÓGICA ORIGINAL (JS)
     ======================= -->
<script>
async function api(action, opts = {}) {
  const headers = opts.headers || {};
  headers['Content-Type'] = 'application/json';
  const admin_csrf = localStorage.getItem('admin_csrf') || '';
  if (admin_csrf) headers['X-CSRF-Token'] = admin_csrf;

  const qs = opts.query ? '&' + opts.query : '';
  const res = await fetch('/Supermercado/backend/public/api.php?action=' + action + qs, {
    method: opts.method || 'GET',
    headers,
    body: opts.body ? JSON.stringify(opts.body) : undefined
  });
  return await res.json();
}

async function loadCoupons() {
  const json = await api('list_coupons');
  const tbody = document.querySelector('#table tbody');
  tbody.innerHTML = '';

  if (!json.coupons || json.coupons.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-muted">No hay cupones</td></tr>';
    return;
  }

  json.coupons.forEach(c => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="checkbox" class="couponCheck" data-id="${c.id}"></td>
      <td>${c.id}</td>
      <td>${c.code}</td>
      <td>${c.type}</td>
      <td>${c.value}</td>
      <td>${c.active ? 'Sí' : 'No'}</td>
      <td>${c.expires_at || '-'}</td>
      <td>
        <button class="btn btn-sm btn-primary edit" data-id="${c.id}">Editar</button>
        <button class="btn btn-sm btn-danger del" data-id="${c.id}">Eliminar</button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  document.querySelectorAll('.edit').forEach(b =>
    b.addEventListener('click', e => openEdit(e.target.dataset.id))
  );
  document.querySelectorAll('.del').forEach(b =>
    b.addEventListener('click', e => delCoupon(e.target.dataset.id))
  );

  document.querySelectorAll('.couponCheck').forEach(cb =>
    cb.addEventListener('change', ev => {
      ev.target.closest('tr').classList.toggle('selected', ev.target.checked);
      updateSelectAllCouponsState();
    })
  );

  document.getElementById('selectAllCoupons').checked = false;
}

function updateSelectAllCouponsState() {
  const all = [...document.querySelectorAll('.couponCheck')];
  const checked = all.filter(c => c.checked).length;
  const sel = document.getElementById('selectAllCoupons');
  sel.indeterminate = checked > 0 && checked < all.length;
  sel.checked = checked === all.length;
}

document.getElementById('selectAllCoupons').addEventListener('change', e => {
  document.querySelectorAll('.couponCheck').forEach(cb => {
    cb.checked = e.target.checked;
    cb.closest('tr').classList.toggle('selected', e.target.checked);
  });
});

function openNew() {
  document.getElementById('cid').value = '';
  document.getElementById('ccode').value = '';
  document.getElementById('cvalue').value = '';
  document.getElementById('cexpires').value = '';
  document.getElementById('cactive').checked = true;
  document.getElementById('formTitle').innerText = 'Nuevo cupón';
  new bootstrap.Modal(formModal).show();
}

async function openEdit(id) {
  const json = await api('get_coupon', { query: 'id=' + id });
  const c = json.coupon;

  document.getElementById('cid').value = c.id;
  document.getElementById('ccode').value = c.code;
  document.getElementById('ctype').value = c.type;
  document.getElementById('cvalue').value = c.value;
  document.getElementById('cexpires').value = c.expires_at ? c.expires_at.replace(' ', 'T') : '';
  document.getElementById('cactive').checked = c.active == 1;
  document.getElementById('formTitle').innerText = 'Editar cupón';
  new bootstrap.Modal(formModal).show();
}

async function save() {
  const body = {
    code: ccode.value,
    type: ctype.value,
    value: cvalue.value,
    expires_at: cexpires.value ? cexpires.value.replace('T', ' ') : null,
    active: cactive.checked ? 1 : 0
  };

  if (cid.value) {
    body.id = cid.value;
    await api('coupon_update', { method: 'POST', body });
  } else {
    await api('coupon_create', { method: 'POST', body });
  }

  bootstrap.Modal.getInstance(formModal).hide();
  loadCoupons();
}

async function delCoupon(id) {
  if (!confirm('¿Eliminar cupón?')) return;
  await api('coupon_delete', { method: 'POST', query: 'id=' + id });
  loadCoupons();
}

document.getElementById('newBtn').addEventListener('click', openNew);
document.getElementById('saveBtn').addEventListener('click', save);
document.getElementById('exportCouponsSelectedBtn').addEventListener('click', () => {
  window.alert('Exportación seleccionada lista para implementar');
});
document.getElementById('exportCouponsFilteredBtn').addEventListener('click', () => {
  window.open('/Supermercado/backend/public/api.php?action=list_coupons&format=csv');
});

loadCoupons();
</script>

</body>
</html>
