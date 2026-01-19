<?php
// Admin – Cupones (PRO)
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
  <title>Admin | Cupones</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="/Supermercado/frontend/public/css/admin.css">

  <style>
    body { overflow-x: hidden; }

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

    .badge-type{
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
  <a href="orders.php">🧾 Pedidos</a>
  <a href="coupons.php" class="active">🏷️ Cupones</a>
  <a href="reports.php">📈 Reportes</a>

  <hr class="text-secondary my-4">

  <a href="logout.php" class="text-danger fw-semibold">🚪 Cerrar sesión</a>
</div>

<!-- CONTENT -->
<div class="content">

  <!-- TOPBAR -->
  <nav class="navbar bg-white shadow-sm px-4" style="height:64px">
    <span class="navbar-text fw-semibold">
      Gestión de cupones y promociones
    </span>
  </nav>

  <div class="container-fluid px-4 mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-0">Cupones</h2>
        <p class="text-muted small mb-0">
          Descuentos, promociones y campañas activas
        </p>
      </div>

      <button id="newBtn" class="btn btn-success">
        ➕ Nuevo cupón
      </button>
    </div>

    <!-- ACTIONS -->
    <div class="d-flex gap-2 mb-3">
      <button id="exportCouponsSelectedBtn" class="btn btn-outline-success btn-sm">
        Exportar seleccionados
      </button>
      <button id="exportCouponsFilteredBtn" class="btn btn-outline-secondary btn-sm">
        Exportar todos
      </button>
    </div>

    <!-- TABLE -->
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table">
          <thead>
            <tr>
              <th width="40">
                <input type="checkbox" id="selectAllCoupons">
              </th>
              <th>ID</th>
              <th>Código</th>
              <th>Tipo</th>
              <th>Valor</th>
              <th>Estado</th>
              <th>Expira</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                Cargando cupones...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 id="formTitle" class="modal-title fw-bold">Nuevo cupón</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="cid">

        <div class="mb-2">
          <label class="form-label">Código</label>
          <input id="ccode" class="form-control" placeholder="EJ: DESCUENTO10">
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
          <input id="cvalue" type="number" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Fecha de expiración</label>
          <input id="cexpires" type="datetime-local" class="form-control">
        </div>

        <div class="form-check mt-2">
          <input id="cactive" class="form-check-input" type="checkbox" checked>
          <label class="form-check-label">Cupón activo</label>
        </div>
      </div>

      <div class="modal-footer">
        <button id="saveBtn" class="btn btn-primary w-100">
          Guardar cupón
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
async function api(action, opts = {}) {
  const headers = { 'Content-Type':'application/json' };
  const csrf = localStorage.getItem('admin_csrf');
  if (csrf) headers['X-CSRF-Token'] = csrf;

  const qs = opts.query ? '&' + opts.query : '';
  const res = await fetch(
    '/Supermercado/backend/public/api.php?action=' + action + qs,
    { method: opts.method || 'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined }
  );
  return await res.json();
}

async function loadCoupons(){
  const json = await api('list_coupons');
  const tbody = document.querySelector('#table tbody');
  tbody.innerHTML = '';

  if (!json.coupons || json.coupons.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center text-muted py-4">
          No hay cupones registrados
        </td>
      </tr>`;
    return;
  }

  json.coupons.forEach(c => {
    tbody.innerHTML += `
      <tr>
        <td><input type="checkbox" class="couponCheck"></td>
        <td>${c.id}</td>
        <td><strong>${c.code}</strong></td>
        <td>
          <span class="badge bg-secondary badge-type">
            ${c.type === 'percent' ? '%' : '$'}
          </span>
        </td>
        <td>${c.value}</td>
        <td>
          <span class="badge ${c.active ? 'bg-success' : 'bg-danger'}">
            ${c.active ? 'Activo' : 'Inactivo'}
          </span>
        </td>
        <td>${c.expires_at || '-'}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary edit" data-id="${c.id}">Editar</button>
          <button class="btn btn-sm btn-outline-danger del" data-id="${c.id}">Eliminar</button>
        </td>
      </tr>`;
  });

  document.querySelectorAll('.edit').forEach(b =>
    b.onclick = () => openEdit(b.dataset.id)
  );
  document.querySelectorAll('.del').forEach(b =>
    b.onclick = () => delCoupon(b.dataset.id)
  );
}

function openNew(){
  cid.value='';
  ccode.value='';
  cvalue.value='';
  cexpires.value='';
  cactive.checked=true;
  formTitle.innerText='Nuevo cupón';
  new bootstrap.Modal(formModal).show();
}

async function openEdit(id){
  const json = await api('get_coupon',{query:'id='+id});
  const c = json.coupon;

  cid.value=c.id;
  ccode.value=c.code;
  ctype.value=c.type;
  cvalue.value=c.value;
  cexpires.value=c.expires_at?.replace(' ','T')||'';
  cactive.checked=c.active==1;

  formTitle.innerText='Editar cupón';
  new bootstrap.Modal(formModal).show();
}

async function save(){
  const body={
    code:ccode.value,
    type:ctype.value,
    value:cvalue.value,
    expires_at:cexpires.value?cexpires.value.replace('T',' '):null,
    active:cactive.checked?1:0
  };

  if(cid.value){
    body.id=cid.value;
    await api('coupon_update',{method:'POST',body});
  }else{
    await api('coupon_create',{method:'POST',body});
  }

  bootstrap.Modal.getInstance(formModal).hide();
  loadCoupons();
}

async function delCoupon(id){
  if(!confirm('¿Eliminar cupón?')) return;
  await api('coupon_delete',{method:'POST',query:'id='+id});
  loadCoupons();
}

newBtn.onclick=openNew;
saveBtn.onclick=save;

loadCoupons();
</script>

</body>
</html>
