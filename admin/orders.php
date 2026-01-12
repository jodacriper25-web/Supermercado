<?php
// Gestión de pedidos (admin)
?>
<?php $title = 'Admin - Pedidos'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php'; ?>
<div class="container mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1>Pedidos</h1>
    <div id="ordersCount" class="text-muted small"></div>
  </div>

  <div class="d-flex gap-2 mb-3 align-items-center">
    <input id="ordersSearch" class="form-control" placeholder="Buscar por ID, cliente o email" style="max-width:360px">
    <select id="ordersStatus" class="form-select" style="max-width:180px">
      <option value="">Todos</option>
      <option value="pending">Pendiente</option>
      <option value="preparing">En preparación</option>
      <option value="sent">Enviado</option>
      <option value="delivered">Entregado</option>
    </select>
    <input id="ordersStart" type="date" class="form-control" style="max-width:160px">
    <input id="ordersEnd" type="date" class="form-control" style="max-width:160px">
    <button id="ordersLoadBtn" class="btn btn-primary">Filtrar</button>
    <button id="ordersClearBtn" class="btn btn-outline-secondary">Limpiar</button>
    <button id="exportSelectedBtn" class="btn btn-outline-success">Exportar seleccionados</button>
    <button id="exportFilteredBtn" class="btn btn-outline-success">Exportar filtrados</button>
    <div id="ordersSpinner" class="spinner-border text-primary ms-2" role="status" style="display:none"><span class="visually-hidden">Cargando...</span></div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover" id="table">
      <thead><tr><th><input type="checkbox" id="selectAllOrders"></th><th data-sort="id" class="sortable">ID</th><th data-sort="customer" class="sortable">Cliente</th><th data-sort="total" class="sortable">Total</th><th data-sort="status" class="sortable">Estado</th><th data-sort="created_at" class="sortable">Fecha</th><th>Acciones</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>

  <nav aria-label="Paginación">
    <ul id="ordersPagination" class="pagination"></ul>
  </nav>

  <!-- Modal order details -->
  <div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Pedido</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="orderBody"></div>
        <div class="modal-footer"><select id="statusSelect" class="form-select me-2" style="max-width:220px"><option value="pending">pending</option><option value="preparing">preparing</option><option value="sent">sent</option><option value="delivered">delivered</option></select><button id="updateStatusBtn" class="btn btn-primary">Actualizar estado</button></div>
      </div>
    </div>
  </div>

<script>
function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function statusColor(status){ switch(status){ case 'pending': return 'warning'; case 'preparing': return 'info'; case 'sent': return 'primary'; case 'delivered': return 'success'; default: return 'secondary'; } }

async function api(action, opts={}){
  const headers = opts.headers || {};
  headers['Content-Type'] = 'application/json';
  const admin_csrf = localStorage.getItem('admin_csrf') || '';
  if (admin_csrf) headers['X-CSRF-Token'] = admin_csrf;
  const qs = opts.query ? '&' + opts.query : '';
  const res = await fetch('/Supermercado/backend/public/api.php?action='+action + qs, {method: opts.method||'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined});
  return await res.json();
}

let currentPage = 1, perPage = 20;
async function loadOrders(page = 1){
  currentPage = page;
  const q = document.getElementById('ordersSearch').value.trim();
  const status = document.getElementById('ordersStatus').value;
  const start = document.getElementById('ordersStart').value;
  const end = document.getElementById('ordersEnd').value;
  const qs = [];
  qs.push('page='+page);
  qs.push('per_page='+perPage);
  if (q) qs.push('q='+encodeURIComponent(q));
  if (status) qs.push('status='+encodeURIComponent(status));
  if (start) qs.push('start='+encodeURIComponent(start));
  if (end) qs.push('end='+encodeURIComponent(end));
  document.getElementById('ordersSpinner').style.display = 'inline-block';
  document.getElementById('ordersCount').innerText = 'Cargando...';
  try{
    const json = await api('orders',{query: qs.join('&')});
    const tbody = document.querySelector('#table tbody'); tbody.innerHTML = '';
    if (!json.orders || json.orders.length===0){ tbody.innerHTML = '<tr><td colspan="7" class="text-muted">No se encontraron pedidos</td></tr>'; document.getElementById('ordersCount').innerText = '0 pedidos'; renderPagination(0, currentPage, perPage, 'ordersPagination', loadOrders); document.getElementById('ordersSpinner').style.display='none'; return; }
    json.orders.forEach(o=>{ const tr = document.createElement('tr'); tr.innerHTML = `<td><input type="checkbox" class="orderCheck" data-id="${o.id}"></td><td>${o.id}</td><td>${escapeHtml(o.customer_name)}</td><td>$${parseFloat(o.total).toFixed(2)}</td><td><span class="badge bg-${statusColor(o.status)}">${escapeHtml(o.status)}</span></td><td>${escapeHtml(o.created_at)}</td><td><button class="btn btn-sm btn-info view" data-id="${o.id}">Ver</button></td>`; tbody.appendChild(tr); });
    // bind selection handlers
    document.querySelectorAll('.orderCheck').forEach(cb=>cb.addEventListener('change', (ev)=>{ ev.target.closest('tr').classList.toggle('selected', ev.target.checked); updateSelectAllState(); }));
    document.getElementById('selectAllOrders').checked = false;
    document.querySelectorAll('.view').forEach(b=>b.addEventListener('click', e=>viewOrder(e.target.dataset.id)));
    document.getElementById('ordersCount').innerText = `Mostrando ${json.orders.length} de ${json.total} pedidos`;
    renderPagination(json.total, currentPage, perPage, 'ordersPagination', loadOrders);
  }catch(e){ console.error(e); document.querySelector('#table tbody').innerHTML = '<tr><td colspan="7" class="text-danger">Error al cargar pedidos</td></tr>'; }
  document.getElementById('ordersSpinner').style.display = 'none';
}

// Pagination and sorting utilities
function renderPagination(total, page, perPage, containerId, onPage){
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  const ul = document.getElementById(containerId);
  if (!ul) return;
  ul.innerHTML = '';
  const makeLi = (txt, p, disabled=false, active=false) => {
    const li = document.createElement('li');
    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
    li.innerHTML = `<a class="page-link" href="#">${txt}</a>`;
    if (!disabled && !active) li.addEventListener('click', (ev)=>{ ev.preventDefault(); onPage(p); });
    return li;
  };
  ul.appendChild(makeLi('Prev', Math.max(1, page-1), page<=1));
  const start = Math.max(1, page-3);
  const end = Math.min(totalPages, page+3);
  for (let i = start; i<=end; i++){ ul.appendChild(makeLi(i, i, false, i===page)); }
  ul.appendChild(makeLi('Next', Math.min(totalPages, page+1), page>=totalPages));
}

// viewOrder unchanged...
}

function renderPagination(total, page, perPage, containerId, onPage){
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  const ul = document.getElementById(containerId);
  if (!ul) return;
  ul.innerHTML = '';
  const makeLi = (txt, p, disabled=false, active=false) => {
    const li = document.createElement('li');
    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
    li.innerHTML = `<a class="page-link" href="#">${txt}</a>`;
    if (!disabled && !active) li.addEventListener('click', (ev)=>{ ev.preventDefault(); onPage(p); });
    return li;
  };
  ul.appendChild(makeLi('Prev', Math.max(1, page-1), page<=1));
  const start = Math.max(1, page-3);
  const end = Math.min(totalPages, page+3);
  for (let i = start; i<=end; i++){ ul.appendChild(makeLi(i, i, false, i===page)); }
  ul.appendChild(makeLi('Next', Math.min(totalPages, page+1), page>=totalPages));
}

async function viewOrder(id){
  const res = await fetch('/Supermercado/backend/public/api.php?action=order&id='+id); const data = await res.json(); const o = data.order; if (!o){ alert('Pedido no encontrado'); return; }
  let html = `<p><strong>Cliente:</strong> ${escapeHtml(o.customer_name)} (${escapeHtml(o.email)})</p><p><strong>Total:</strong> $${parseFloat(o.total).toFixed(2)}</p>`;
  if (o.invoice_number){ html += `<p>Factura: <a href="/Supermercado/invoices/${o.invoice_number}.pdf" target="_blank">${escapeHtml(o.invoice_number)}</a></p>`; }
  html += `<table class="table"><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th></tr></thead><tbody>`;
  o.items.forEach(it=>{ html += `<tr><td>${escapeHtml(it.name)}</td><td>${it.quantity}</td><td>$${parseFloat(it.price).toFixed(2)}</td></tr>`; }); html += '</tbody></table>';
  document.getElementById('orderBody').innerHTML = html; document.getElementById('statusSelect').value = o.status || 'pending'; new bootstrap.Modal(document.getElementById('orderModal')).show();
  document.getElementById('updateStatusBtn').onclick = async ()=>{ const newStatus = document.getElementById('statusSelect').value; const r = await api('update_order_status',{method:'POST', body:{order_id: o.id, status: newStatus}}); if (r.success) { toast('Estado actualizado'); loadOrders(currentPage); bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide(); } else toast('Error al actualizar'); };
}

// Selection helpers
function updateSelectAllState(){ const all = Array.from(document.querySelectorAll('.orderCheck')); if (all.length === 0) { document.getElementById('selectAllOrders').checked = false; return; } const checked = all.filter(c=>c.checked).length; document.getElementById('selectAllOrders').indeterminate = checked>0 && checked<all.length; document.getElementById('selectAllOrders').checked = checked === all.length; }

document.getElementById('selectAllOrders').addEventListener('change', (e)=>{ const checked = e.target.checked; document.querySelectorAll('.orderCheck').forEach(cb=>{ cb.checked = checked; cb.closest('tr').classList.toggle('selected', checked); }); });

function downloadCSV(filename, rows){ const csv = rows.map(r => r.map(v => '"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n'); const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'}); const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = filename; document.body.appendChild(link); link.click(); link.remove(); }

function exportSelected(){ const checked = Array.from(document.querySelectorAll('.orderCheck')).filter(c=>c.checked); if (checked.length === 0){ toast('Seleccione pedidos para exportar'); return; } const rows = [['ID','Cliente','Total','Estado','Fecha']]; checked.forEach(cb=>{ const tr = cb.closest('tr'); const cells = tr.querySelectorAll('td'); rows.push([cells[1].innerText.trim(), cells[2].innerText.trim(), cells[3].innerText.trim(), cells[4].innerText.trim(), cells[5].innerText.trim()]); }); const fname = 'orders_selected_'+new Date().toISOString().slice(0,10)+'.csv'; downloadCSV(fname, rows); }

document.getElementById('exportSelectedBtn').addEventListener('click', exportSelected);

document.getElementById('exportFilteredBtn').addEventListener('click', ()=>{
  const q = document.getElementById('ordersSearch').value.trim(); const status = document.getElementById('ordersStatus').value; const start = document.getElementById('ordersStart').value; const end = document.getElementById('ordersEnd').value; const qs = [];
  if (q) qs.push('q='+encodeURIComponent(q)); if (status) qs.push('status='+encodeURIComponent(status)); if (start) qs.push('start='+encodeURIComponent(start)); if (end) qs.push('end='+encodeURIComponent(end)); qs.push('format=csv'); const url = '/Supermercado/backend/public/api.php?action=orders&'+qs.join('&'); window.open(url, '_blank'); });

// Sorting
function sortTableByCol(colIndex, numeric=false, asc=true){ const tbody = document.querySelector('#table tbody'); const rows = Array.from(tbody.querySelectorAll('tr')); rows.sort((a,b)=>{ const va = a.children[colIndex].innerText.trim(); const vb = b.children[colIndex].innerText.trim(); if (numeric){ return asc ? (parseFloat(va.replace(/[^0-9.-]+/g,'')) - parseFloat(vb.replace(/[^0-9.-]+/g,''))) : (parseFloat(vb.replace(/[^0-9.-]+/g,'')) - parseFloat(va.replace(/[^0-9.-]+/g,''))); } return asc ? va.localeCompare(vb) : vb.localeCompare(va); }); rows.forEach(r=>tbody.appendChild(r)); }

document.querySelectorAll('.sortable').forEach((th, idx)=>{ th.addEventListener('click', ()=>{ const key = th.getAttribute('data-sort'); const colMap = {id:1, customer:2, total:3, status:4, created_at:5}; const col = colMap[key]; const asc = !(th.dataset.asc === '1'); th.dataset.asc = asc? '1':'0'; sortTableByCol(col, key==='total', asc); }); });

// Filters
document.getElementById('ordersLoadBtn').addEventListener('click', ()=> loadOrders(1));
document.getElementById('ordersClearBtn').addEventListener('click', ()=>{ document.getElementById('ordersSearch').value=''; document.getElementById('ordersStatus').value=''; document.getElementById('ordersStart').value=''; document.getElementById('ordersEnd').value=''; loadOrders(1); });
document.getElementById('ordersSearch').addEventListener('keydown', (e)=>{ if (e.key === 'Enter') { e.preventDefault(); loadOrders(1); } });

loadOrders();
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/footer.php'; ?>