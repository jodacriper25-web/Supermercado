<?php
// CRUD cupones (admin)
?>
<?php $title = 'Admin - Cupones'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php'; ?>
<div class="container mt-4">
  <h1>Cupones</h1>
  <div class="mb-3 d-flex gap-2">
    <button id="newBtn" class="btn btn-success">Nuevo cupón</button>
    <button id="exportCouponsSelectedBtn" class="btn btn-outline-success">Exportar seleccionados</button>
    <button id="exportCouponsFilteredBtn" class="btn btn-outline-success">Exportar filtrados</button>
    <a class="btn btn-secondary ms-auto" href="/Supermercado/admin/dashboard.php">Dashboard</a>
  </div>
  <table class="table" id="table">
    <thead><tr><th><input type="checkbox" id="selectAllCoupons"></th><th>ID</th><th>Codigo</th><th>Tipo</th><th>Valor</th><th>Activo</th><th>Expira</th><th>Acciones</th></tr></thead>
    <tbody></tbody>
  </table>

  <!-- Modal Form -->
  <div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 id="formTitle" class="modal-title">Nuevo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" id="cid">
          <div class="mb-2"><input id="ccode" class="form-control" placeholder="Código"></div>
          <div class="mb-2"><select id="ctype" class="form-select"><option value="percent">percent</option><option value="fixed">fixed</option></select></div>
          <div class="mb-2"><input id="cvalue" class="form-control" placeholder="Valor"></div>
          <div class="mb-2"><input id="cexpires" type="datetime-local" class="form-control" placeholder="Expires"></div>
          <div class="form-check"><input id="cactive" class="form-check-input" type="checkbox" checked><label class="form-check-label">Activo</label></div>
        </div>
        <div class="modal-footer"><button id="saveBtn" class="btn btn-primary">Guardar</button></div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function api(action, opts={}){
  const headers = opts.headers || {};
  headers['Content-Type'] = 'application/json';
  const admin_csrf = localStorage.getItem('admin_csrf') || '';
  if (admin_csrf) headers['X-CSRF-Token'] = admin_csrf;
  const res = await fetch('/Supermercado/backend/public/api.php?action='+action, {method: opts.method||'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined});
  return await res.json();
}

async function loadCoupons(){ const json = await api('list_coupons'); const tbody = document.querySelector('#table tbody'); tbody.innerHTML=''; if (!json.coupons || json.coupons.length===0){ tbody.innerHTML='<tr><td colspan="8" class="text-muted">No hay cupones</td></tr>'; return; } json.coupons.forEach(c=>{ const tr = document.createElement('tr'); tr.innerHTML = `<td><input type="checkbox" class="couponCheck" data-id="${c.id}"></td><td>${c.id}</td><td>${c.code}</td><td>${c.type}</td><td>${c.value}</td><td>${c.active}</td><td>${c.expires_at}</td><td><button class="btn btn-sm btn-primary edit" data-id="${c.id}">Editar</button> <button class="btn btn-sm btn-danger del" data-id="${c.id}">Eliminar</button></td>`; tbody.appendChild(tr); });
  document.querySelectorAll('.edit').forEach(b=>b.addEventListener('click', e=>openEdit(e.target.dataset.id))); document.querySelectorAll('.del').forEach(b=>b.addEventListener('click', e=>delCoupon(e.target.dataset.id)));
  // selection handlers
  document.querySelectorAll('.couponCheck').forEach(cb=>cb.addEventListener('change', (ev)=>{ ev.target.closest('tr').classList.toggle('selected', ev.target.checked); updateSelectAllCouponsState(); }));
  document.getElementById('selectAllCoupons').checked = false; } 

function updateSelectAllCouponsState(){ const all = Array.from(document.querySelectorAll('.couponCheck')); if (all.length === 0) { document.getElementById('selectAllCoupons').checked = false; return; } const checked = all.filter(c=>c.checked).length; document.getElementById('selectAllCoupons').indeterminate = checked>0 && checked<all.length; document.getElementById('selectAllCoupons').checked = checked === all.length; }

document.getElementById('selectAllCoupons').addEventListener('change', (e)=>{ const checked = e.target.checked; document.querySelectorAll('.couponCheck').forEach(cb=>{ cb.checked = checked; cb.closest('tr').classList.toggle('selected', checked); }); });

function exportCouponSelection(){ const checked = Array.from(document.querySelectorAll('.couponCheck')).filter(c=>c.checked); if (checked.length === 0){ toast('Seleccione cupones para exportar'); return; } const rows = [['ID','Código','Tipo','Valor','Activo','Expira']]; checked.forEach(cb=>{ const tr = cb.closest('tr'); const cells = tr.querySelectorAll('td'); rows.push([cells[1].innerText.trim(), cells[2].innerText.trim(), cells[3].innerText.trim(), cells[4].innerText.trim(), cells[5].innerText.trim(), cells[6].innerText.trim()]); }); const fname = 'coupons_selected_'+new Date().toISOString().slice(0,10)+'.csv'; downloadCSV(fname, rows); }

document.getElementById('exportCouponsSelectedBtn').addEventListener('click', exportCouponSelection);

document.getElementById('exportCouponsFilteredBtn').addEventListener('click', ()=>{ const url = '/Supermercado/backend/public/api.php?action=list_coupons&format=csv'; window.open(url, '_blank'); });

function openNew(){ document.getElementById('cid').value=''; document.getElementById('ccode').value=''; document.getElementById('cvalue').value=''; document.getElementById('cexpires').value=''; document.getElementById('formTitle').innerText='Nuevo'; new bootstrap.Modal(document.getElementById('formModal')).show(); }

async function openEdit(id){ const res = await api('get_coupon',{query:'id='+id}); const json = await res; const c = json.coupon; document.getElementById('cid').value=c.id; document.getElementById('ccode').value=c.code; document.getElementById('ctype').value=c.type; document.getElementById('cvalue').value=c.value; document.getElementById('cexpires').value = c.expires_at ? c.expires_at.replace(' ','T') : ''; document.getElementById('cactive').checked = c.active==1; document.getElementById('formTitle').innerText='Editar'; new bootstrap.Modal(document.getElementById('formModal')).show(); }

function validateCouponForm(){ const code = document.getElementById('ccode').value.trim(); const value = parseFloat(document.getElementById('cvalue').value); if (!code){ toast('Código obligatorio'); return false; } if (isNaN(value) || value < 0){ toast('Valor inválido'); return false; } return true; }

async function save(){ if (!validateCouponForm()) return; const id = document.getElementById('cid').value; const body = {code:document.getElementById('ccode').value, type:document.getElementById('ctype').value, value:document.getElementById('cvalue').value, expires_at:document.getElementById('cexpires').value? document.getElementById('cexpires').value.replace('T',' ') : null, active: document.getElementById('cactive').checked?1:0}; if (id){ body.id = id; const r = await api('coupon_update',{method:'POST', body}); if (r.success) { bootstrap.Modal.getInstance(document.getElementById('formModal')).hide(); loadCoupons(); toast('Cupón actualizado'); } } else { const r = await api('coupon_create',{method:'POST', body}); if (r.success) { bootstrap.Modal.getInstance(document.getElementById('formModal')).hide(); loadCoupons(); toast('Cupón creado'); } } }

async function delCoupon(id){ if (!confirm('Eliminar cupón?')) return; const r = await api('coupon_delete',{method:'POST', query:'id='+id}); if (r.success) loadCoupons(); }

document.getElementById('newBtn').addEventListener('click', openNew);
document.getElementById('saveBtn').addEventListener('click', save);
loadCoupons();
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/footer.php'; ?>