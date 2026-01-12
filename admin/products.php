<?php
// CRUD productos (admin)
?>
<?php $title = 'Admin - Productos'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php'; ?>
<div class="container container-main mt-4">
  <h1>Productos</h1>
  <div class="mb-3 d-flex gap-2">
    <button id="newBtn" class="btn btn-success">Nuevo producto</button>
    <button id="exportProductsSelectedBtn" class="btn btn-outline-success">Exportar seleccionados</button>
    <button id="exportProductsFilteredBtn" class="btn btn-outline-success">Exportar filtrados</button>
    <a class="btn btn-secondary ms-auto" href="/Supermercado/admin/dashboard.php">Dashboard</a>
  </div>
  <table class="table" id="table">
    <thead><tr><th><input type="checkbox" id="selectAllProducts"></th><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
    <tbody></tbody>
  </table>
  <nav aria-label="Paginación">
    <ul id="prodPagination" class="pagination"></ul>
  </nav>

  <!-- Modal Form -->
  <div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 id="formTitle" class="modal-title">Nuevo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" id="pid">
          <div class="mb-2"><input id="pname" class="form-control" placeholder="Nombre"></div>
          <div class="mb-2"><input id="pprice" class="form-control" placeholder="Precio"></div>
          <div class="mb-2"><input id="pstock" class="form-control" placeholder="Stock"></div>
          <div class="mb-2"><select id="pcat" class="form-select"></select></div>
        </div>
        <div class="modal-footer"><button id="saveBtn" class="btn btn-primary">Guardar</button></div>
      </div>
    </div>
  </div>

<script>
async function api(action, opts={}){
  const headers = opts.headers || {};
  headers['Content-Type'] = 'application/json';
  const admin_csrf = localStorage.getItem('admin_csrf') || '';
  if (admin_csrf) headers['X-CSRF-Token'] = admin_csrf;
  const qs = opts.query ? '&' + opts.query : '';
  const res = await fetch('/Supermercado/backend/public/api.php?action='+action + qs, {method: opts.method||'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined});
  return await res.json();
}

let currentPage = 1; const perPage = 10;
async function loadProducts(page = 1){
  currentPage = page;
  const q = document.getElementById('prodSearch') ? document.getElementById('prodSearch').value.trim() : '';
  const json = await api('products', {query: 'page='+page+'&per_page='+perPage + (q? '&q='+encodeURIComponent(q) : '')});
  const tbody = document.querySelector('#table tbody'); tbody.innerHTML = '';
  if (!json.products || json.products.length === 0){ tbody.innerHTML = '<tr><td colspan="7" class="text-muted">No hay productos</td></tr>'; renderPagination(0, currentPage, perPage, 'prodPagination', loadProducts); return; }
  json.products.forEach(p=>{ const tr = document.createElement('tr'); tr.innerHTML = `<td><input type="checkbox" class="prodCheck" data-id="${p.id}"></td><td>${p.id}</td><td>${p.name}</td><td>${p.category}</td><td>$${p.price}</td><td>${p.stock}</td><td><button class="btn btn-sm btn-primary edit" data-id="${p.id}">Editar</button> <button class="btn btn-sm btn-danger del" data-id="${p.id}">Eliminar</button></td>`; tbody.appendChild(tr); });
  // bind selection handlers
  document.querySelectorAll('.prodCheck').forEach(cb=>cb.addEventListener('change', (ev)=>{ ev.target.closest('tr').classList.toggle('selected', ev.target.checked); updateSelectAllProductsState(); }));
  document.getElementById('selectAllProducts').checked = false;
  document.querySelectorAll('.edit').forEach(b=>b.addEventListener('click', e=>openEdit(e.target.dataset.id)));
  document.querySelectorAll('.del').forEach(b=>b.addEventListener('click', e=>delProduct(e.target.dataset.id)));
  renderPagination(json.total, currentPage, perPage, 'prodPagination', loadProducts);
}

function updateSelectAllProductsState(){ const all = Array.from(document.querySelectorAll('.prodCheck')); if (all.length === 0) { document.getElementById('selectAllProducts').checked = false; return; } const checked = all.filter(c=>c.checked).length; document.getElementById('selectAllProducts').indeterminate = checked>0 && checked<all.length; document.getElementById('selectAllProducts').checked = checked === all.length; }

document.getElementById('selectAllProducts').addEventListener('change', (e)=>{ const checked = e.target.checked; document.querySelectorAll('.prodCheck').forEach(cb=>{ cb.checked = checked; cb.closest('tr').classList.toggle('selected', checked); }); });

function downloadCSV(filename, rows){ const csv = rows.map(r => r.map(v => '"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n'); const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'}); const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = filename; document.body.appendChild(link); link.click(); link.remove(); }

function exportProductSelection(){ const checked = Array.from(document.querySelectorAll('.prodCheck')).filter(c=>c.checked); if (checked.length === 0){ toast('Seleccione productos para exportar'); return; } const rows = [['ID','Nombre','Categoría','Precio','Stock']]; checked.forEach(cb=>{ const tr = cb.closest('tr'); const cells = tr.querySelectorAll('td'); rows.push([cells[1].innerText.trim(), cells[2].innerText.trim(), cells[3].innerText.trim(), cells[4].innerText.trim(), cells[5].innerText.trim()]); }); const fname = 'products_selected_'+new Date().toISOString().slice(0,10)+'.csv'; downloadCSV(fname, rows); }

document.getElementById('exportProductsSelectedBtn').addEventListener('click', exportProductSelection);

document.getElementById('exportProductsFilteredBtn').addEventListener('click', ()=>{ const q = document.getElementById('prodSearch') ? document.getElementById('prodSearch').value.trim() : ''; const qs = [];
  if (q) qs.push('q='+encodeURIComponent(q)); qs.push('format=csv'); const url = '/Supermercado/backend/public/api.php?action=products&'+qs.join('&'); window.open(url, '_blank'); });

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

async function loadCategories(){ const res = await api('products'); const json=await res; const sel = document.getElementById('pcat'); sel.innerHTML = '<option value="1">Sin seleccionar</option>'; }

function openNew(){ document.getElementById('pid').value=''; document.getElementById('pname').value=''; document.getElementById('pprice').value=''; document.getElementById('pstock').value=''; document.getElementById('formTitle').innerText='Nuevo'; new bootstrap.Modal(document.getElementById('formModal')).show(); }

async function openEdit(id){ const res = await fetch('/Supermercado/backend/public/api.php?action=product&id='+id); const json=await res.json(); const p = json.product; document.getElementById('pid').value=p.id; document.getElementById('pname').value=p.name; document.getElementById('pprice').value=p.price; document.getElementById('pstock').value=p.stock; document.getElementById('pcat').value = p.category_id || 1; document.getElementById('formTitle').innerText='Editar'; new bootstrap.Modal(document.getElementById('formModal')).show(); }

function validateProductForm(){ const name = document.getElementById('pname').value.trim(); const price = parseFloat(document.getElementById('pprice').value); const stock = parseInt(document.getElementById('pstock').value||'0'); if (!name){ toast('El nombre es obligatorio'); return false; } if (isNaN(price) || price < 0){ toast('Ingrese un precio válido'); return false; } if (isNaN(stock) || stock < 0){ toast('Ingrese stock válido'); return false; } return true; }

async function save(){ if (!validateProductForm()) return; const id = document.getElementById('pid').value; const body = {name:document.getElementById('pname').value, price:document.getElementById('pprice').value, stock:document.getElementById('pstock').value, category_id: document.getElementById('pcat').value}; if (id){ body.id = id; const r = await api('product_update', {method:'POST', body}); if (r.success) { bootstrap.Modal.getInstance(document.getElementById('formModal')).hide(); loadProducts(currentPage); toast('Producto actualizado'); } } else { const r = await api('product_create', {method:'POST', body}); if (r.success) { bootstrap.Modal.getInstance(document.getElementById('formModal')).hide(); loadProducts(currentPage); toast('Producto creado'); } } }

async function delProduct(id){ if (!confirm('Eliminar producto?')) return; const r = await api('product_delete',{method:'POST', query: 'id='+id}); if (r.success) loadProducts(currentPage); }

document.getElementById('newBtn').addEventListener('click', openNew);
document.getElementById('saveBtn').addEventListener('click', save);
loadProducts(); loadCategories();
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/footer.php'; ?>