document.addEventListener('DOMContentLoaded', function(){
  function getCookie(name){
    let v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return v ? v[2] : '';
  }

  async function apiFetch(url, opts={}){
    opts.credentials = opts.credentials || 'same-origin';
    if (opts.method && opts.method.toUpperCase() !== 'GET'){
      const csrftoken = getCookie('csrftoken');
      opts.headers = Object.assign({'X-CSRFToken': csrftoken, 'Content-Type':'application/json'}, opts.headers || {});
    }
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error('Network error');
    return await res.json();
  }

  let searchTimeout = null;
  let currentPage = 1, perPage = 12, loadingMore = false, hasMore = true, lastQuery = '';

  async function loadProducts(q = '', page = 1, append = false){
    lastQuery = q;
    loadingMore = true;
    const loader = document.getElementById('loader'); if (loader) loader.style.display = 'inline-block';
    const loadMoreContainer = document.getElementById('loadMoreContainer'); if (loadMoreContainer) loadMoreContainer.style.display = 'none';
    const url = '/api/products/?page='+page+'&per_page='+perPage + (q? '&q='+encodeURIComponent(q) : '');
    try{
      const json = await apiFetch(url);
      const container = document.getElementById('products');
      if (!append && container) container.innerHTML = '';
      const products = json.products || json;
      if (!products || products.length === 0){
        if (!append && container) container.innerHTML = '<p class="text-muted">No se encontraron productos.</p>';
        hasMore = false;
        if (loader) loader.style.display = 'none';
        loadingMore = false;
        return;
      }
      products.forEach(p => {
        const col = document.createElement('div'); col.className='col-md-3 mb-3';
        col.innerHTML = `
          <div class="card h-100 product-card" data-id="${p.id}">
            <img src="${p.image || '/static/core/img/placeholder.svg'}" class="card-img-top" alt="${p.name}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">${p.name}</h5>
              <p class="card-text mb-2">$${p.price}</p>
              <div class="mt-auto d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary info" data-id="${p.id}">Ver</button>
                <button class="btn btn-sm btn-primary add" data-id="${p.id}">Agregar</button>
              </div>
            </div>
          </div>`;
        if (container) container.appendChild(col);
      });
      document.querySelectorAll('.add').forEach(btn => btn.addEventListener('click', addToCart));
      document.querySelectorAll('.info').forEach(btn => btn.addEventListener('click', viewProduct));
      currentPage = page;
    }catch(err){ console.error(err); const container = document.getElementById('products'); if (!append && container) container.innerHTML = '<p class="text-danger">Error cargando productos.</p>'; }
    if (loader) loader.style.display = 'none';
    loadingMore = false;
  }

  document.getElementById('loadMoreBtn')?.addEventListener('click', ()=>{ if (loadingMore || !hasMore) return; loadProducts(lastQuery, currentPage+1, true); });

  window.addEventListener('scroll', ()=>{
    if (loadingMore || !hasMore) return;
    if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 300)){
      loadProducts(lastQuery, currentPage+1, true);
    }
  });

  function toast(message){
    const tEl = document.getElementById('toast');
    if (!tEl) return; document.getElementById('toast-body').innerText = message;
    const t = new bootstrap.Toast(tEl);
    t.show();
  }

  async function loadPromotions(){
    try{
      const json = await apiFetch('/api/promotions/?limit=5');
      const slides = document.getElementById('promoSlides'); if (slides) slides.innerHTML = '';
      if (!json || !json.promotions || json.promotions.length === 0){ const container = document.getElementById('promoCarouselContainer'); if (container) container.style.display = 'none'; return; }
      json.promotions.forEach((p, idx)=>{
        const active = idx===0 ? ' active' : '';
        const item = document.createElement('div'); item.className = 'carousel-item'+active;
        item.innerHTML = `<img src="${p.image || '/static/core/img/placeholder.svg'}" class="d-block w-100" alt="${p.name}"><div class="carousel-caption d-none d-md-block"><h5>${p.name}</h5><p>${p.description ? p.description.slice(0,80) : ''}</p></div>`;
        slides.appendChild(item);
      });
    }catch(e){ console.error('Promotions error', e); const container = document.getElementById('promoCarouselContainer'); if (container) container.style.display = 'none'; }
  }

  function updateCartCount(){ const cart = JSON.parse(localStorage.getItem('cart')||'{}'); const el = document.getElementById('cart-count'); if (el) el.innerText = Object.values(cart).reduce((a,b)=>a+b,0); }

  function animateButton(btn){ btn.classList.add('btn-success'); setTimeout(()=>btn.classList.remove('btn-success'), 450); }

  function addToCart(e){
    const id = e.target.dataset.id;
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    cart[id] = (cart[id]||0) + 1;
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    animateButton(e.target);
    toast('Producto agregado al carrito');
  }

  async function viewProduct(e){
    const id = e.target.dataset.id;
    try{
      const json = await apiFetch('/api/products/'+id+'/'); const p = json.product || json;
      const html = `<h5>${p.name}</h5><p>${p.description || ''}</p><p><strong>Precio: $${p.price}</strong></p>`;
      const body = document.getElementById('cartBody');
      if (!body) return;
      body.innerHTML = html + '<div class="mt-3"><button id="addFromInfo" class="btn btn-primary">Agregar al carrito</button></div>';
      new bootstrap.Modal(document.getElementById('cartModal')).show();
      document.getElementById('addFromInfo').addEventListener('click', ()=>{ let cart = JSON.parse(localStorage.getItem('cart')||'{}'); cart[id]=(cart[id]||0)+1; localStorage.setItem('cart', JSON.stringify(cart)); updateCartCount(); toast('Producto agregado'); bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide(); });
    }catch(e){ console.error(e); }
  }

  function renderCart(){
    const cart = JSON.parse(localStorage.getItem('cart')||'{}');
    const applied = JSON.parse(localStorage.getItem('applied_coupon')||'null');
    const body = document.getElementById('cartBody');
    if (!body) return;
    if (Object.keys(cart).length === 0){ body.innerHTML = '<p>Carrito vacío</p>'; return; }
    body.innerHTML = '<div id="cartItems" class="list-group mb-3"></div><div class="d-flex gap-2 mb-2"><input id="couponInput" class="form-control" placeholder="Código de cupón"><button id="applyCouponBtn" class="btn btn-outline-primary">Aplicar</button></div><div class="d-flex justify-content-between align-items-center"><div><small id="couponInfo" class="text-success"></small></div><strong>Total: $<span id="cartTotal">0.00</span></strong></div>';
    if (applied){ const ci = document.getElementById('couponInfo'); if (ci) ci.innerHTML = `Cupón aplicado: ${applied.code} (-$${applied.discount}) <button id="removeCouponBtn" class="btn btn-sm btn-link">Quitar</button>`; }
    const itemsDiv = document.getElementById('cartItems');
    itemsDiv.innerHTML = '';
    let total = 0;
    const ids = Object.keys(cart);
    const promises = ids.map(async (id)=>{
      const json = await apiFetch('/api/products/'+id+'/'); const p = json.product || json; const qty = cart[id]; const subtotal = parseFloat(p.price) * qty; total += subtotal;
      const el = document.createElement('div'); el.className='list-group-item d-flex justify-content-between align-items-center';
      el.innerHTML = `<div>
          <div><strong>${p.name}</strong></div>
          <div class="small text-muted">$${p.price} x <button class="btn btn-sm btn-outline-secondary btn-decr" data-id="${id}">-</button> <span class="mx-2 qty" data-id="${id}">${qty}</span> <button class="btn btn-sm btn-outline-secondary btn-incr" data-id="${id}">+</button></div>
        </div>
        <div><button class="btn btn-sm btn-danger btn-remove" data-id="${id}">Eliminar</button><div class="mt-2">$<span class="subtotal" data-id="${id}">${subtotal.toFixed(2)}</span></div></div>`;
      itemsDiv.appendChild(el);
    });
    Promise.all(promises).then(()=>{ 
      const applied = JSON.parse(localStorage.getItem('applied_coupon')||'null');
      let totalFinal = total;
      if (applied){ totalFinal = (total - applied.discount); }
      const totalEl = document.getElementById('cartTotal'); if (totalEl) totalEl.innerText = totalFinal.toFixed(2);
      attachCartButtons();
      const applyBtn = document.getElementById('applyCouponBtn'); if (applyBtn) applyBtn.addEventListener('click', applyCoupon);
      const rem = document.getElementById('removeCouponBtn'); if (rem) rem.addEventListener('click', ()=>{ localStorage.removeItem('applied_coupon'); renderCart(); toast('Cupón removido'); });
    });
  }

  async function applyCoupon(){
    const codeEl = document.getElementById('couponInput'); if (!codeEl) return; const code = codeEl.value.trim();
    if (!code) return; const cart = JSON.parse(localStorage.getItem('cart')||'{}'); let total = 0; for (const id in cart){ const json = await apiFetch('/api/products/'+id+'/'); total += parseFloat(json.product.price||json.price) * cart[id]; }
    const res = await apiFetch('/api/coupons/validate/?code='+encodeURIComponent(code)+'&total='+total);
    if (res.valid){ localStorage.setItem('applied_coupon', JSON.stringify({code:code, discount:res.discount})); const ci = document.getElementById('couponInfo'); if (ci) ci.innerText = `Cupón aplicado: ${code} (-$${res.discount})`; renderCart(); toast('Cupón aplicado'); } else { toast('Cupón inválido: ' + (res.error||'')); }
  }

  function attachCartButtons(){
    document.querySelectorAll('.btn-incr').forEach(b=>b.addEventListener('click', (e)=>{ const id = e.target.dataset.id; let cart = JSON.parse(localStorage.getItem('cart')||'{}'); cart[id] = (cart[id]||0) + 1; localStorage.setItem('cart', JSON.stringify(cart)); renderCart(); updateCartCount(); }));
    document.querySelectorAll('.btn-decr').forEach(b=>b.addEventListener('click', (e)=>{ const id = e.target.dataset.id; let cart = JSON.parse(localStorage.getItem('cart')||'{}'); if (!cart[id]) return; cart[id] = cart[id]-1; if (cart[id] <= 0) delete cart[id]; localStorage.setItem('cart', JSON.stringify(cart)); renderCart(); updateCartCount(); }));
    document.querySelectorAll('.btn-remove').forEach(b=>b.addEventListener('click', (e)=>{ const id = e.target.dataset.id; let cart = JSON.parse(localStorage.getItem('cart')||'{}'); delete cart[id]; localStorage.setItem('cart', JSON.stringify(cart)); renderCart(); updateCartCount(); toast('Producto eliminado'); }));
  }

  document.getElementById('view-cart')?.addEventListener('click', ()=>{ renderCart(); new bootstrap.Modal(document.getElementById('cartModal')).show(); });

  async function doLogin(){
    const email = document.getElementById('email')?.value;
    const password = document.getElementById('password')?.value;
    const res = await apiFetch('/api/auth/login/', {method:'POST', body: JSON.stringify({email,password})});
    if (res.success){
      document.getElementById('loginMsg').innerText = '';
      bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide();
      toast('Sesión iniciada');
    } else {
      const msg = document.getElementById('loginMsg'); if (msg) msg.innerText = 'Credenciales incorrectas';
    }
  }

  document.getElementById('loginBtn')?.addEventListener('click', doLogin);

  async function checkout(){
    const cart = JSON.parse(localStorage.getItem('cart')||'{}');
    if (Object.keys(cart).length===0) { toast('Carrito vacío'); return; }
    const checkoutBtn = document.getElementById('checkoutBtn'); if (checkoutBtn){ checkoutBtn.disabled = true; checkoutBtn.innerText = 'Procesando...'; }
    const items = [];
    let total = 0;
    for (const id in cart){ const json = await apiFetch('/api/products/'+id+'/'); const p = json.product || json; const qty = cart[id]; items.push({product_id:id, quantity:qty}); total += parseFloat(p.price) * qty; }
    try{
      const applied = JSON.parse(localStorage.getItem('applied_coupon')||'null'); const payload = {items, total}; if (applied && applied.code){ payload.coupon_code = applied.code; }
      const res = await apiFetch('/api/orders/create/', {method:'POST', body: JSON.stringify(payload)});
      if (checkoutBtn){ checkoutBtn.disabled = false; checkoutBtn.innerText = 'Pagar'; }
      if (res.success){
        localStorage.removeItem('cart'); localStorage.removeItem('applied_coupon'); updateCartCount(); renderCart(); new bootstrap.Modal(document.getElementById('cartModal'))?.hide(); toast('Pedido creado. Factura: ' + (res.invoice || 'pendiente'));
      } else {
        toast('Error al crear pedido: ' + (res.error || ''));
      }
    }catch(e){ console.error(e); toast('Error en checkout'); if (checkoutBtn){ checkoutBtn.disabled = false; checkoutBtn.innerText = 'Pagar'; } }
  }

  document.getElementById('checkoutBtn')?.addEventListener('click', checkout);

  document.getElementById('searchBtn')?.addEventListener('click', ()=>{ const q = document.getElementById('search')?.value.trim()||''; loadProducts(q); });
  document.getElementById('search')?.addEventListener('keydown', (e)=>{ if (e.key === 'Enter') { e.preventDefault(); document.getElementById('searchBtn')?.click(); } });
  document.getElementById('search')?.addEventListener('input', (e)=>{ clearTimeout(searchTimeout); searchTimeout = setTimeout(()=>{ loadProducts(e.target.value.trim()); }, 400); });

  window.addEventListener('load', ()=>{ loadPromotions(); loadProducts(); const cart = JSON.parse(localStorage.getItem('cart')||'{}'); const el = document.getElementById('cart-count'); if (el) el.innerText = Object.values(cart).reduce((a,b)=>a+b,0); });

});
