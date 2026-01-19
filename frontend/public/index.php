<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Supermercado Yaruquíes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- FRONTEND CSS -->
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>

<body>

<!-- ================= HEADER ================= -->
<header class="main-header shadow-sm">
  <div class="container d-flex align-items-center justify-content-between py-3">

    <div class="brand">
      <i class="bi bi-cart3"></i>
      <span>Supermercado Yaruquíes</span>
    </div>

    <div class="header-actions d-flex gap-2">
      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cartModal">
        <i class="bi bi-cart"></i>
        <span id="cartCount">0</span>
      </button>

     <?php if (isset($_SESSION['client'])): ?>
  <span class="btn btn-outline-secondary disabled">
    <?= htmlspecialchars($_SESSION['client']['name']) ?>
  </span>
  <a href="/Supermercado/frontend/public/logout.php" class="btn btn-outline-danger">
    Salir
  </a>
<?php else: ?>
  <a href="/Supermercado/frontend/public/login.php" class="btn btn-primary">
    Ingresar
  </a>
<?php endif; ?>


  </div>
</header>

<!-- ================= PROMOCIONES ================= -->
<section class="promo-section container mt-4">
  <div id="promoCarousel" class="carousel slide promo-carousel" data-bs-ride="carousel">
    <div class="carousel-inner" id="promoSlides">
      <!-- promos dinámicas desde ADMIN (cupones activos) -->
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
</section>

<!-- ================= MAIN ================= -->
<main class="container main-content">

  <!-- BUSCADOR -->
  <section class="search-bar my-4">
    <div class="input-group">
      <input id="search" type="search" class="form-control" placeholder="Buscar productos...">
      <button id="searchBtn" class="btn btn-primary">
        <i class="bi bi-search"></i>
      </button>
    </div>
    <div id="loader" class="spinner-border text-primary mt-3 d-none"></div>
  </section>

  <!-- PRODUCTOS -->
  <section id="products" class="row g-4 products-grid">
    <!-- productos dinámicos -->
  </section>

  <div id="emptyMsg" class="empty-msg d-none">
    No se encontraron productos 😕
  </div>

  <div id="loadMoreContainer" class="load-more d-none">
    <button id="loadMoreBtn" class="btn btn-outline-primary">
      Cargar más productos
    </button>
  </div>

</main>

<!-- ================= CARRITO ================= -->
<div class="modal fade" id="cartModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5>🛒 Tu carrito</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="cartBody">
        <p class="text-muted text-center">Tu carrito está vacío</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success w-100">
          Proceder al pago
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= LOGIN ================= -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5>🔐 Iniciar sesión</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input id="email" class="form-control mb-2" placeholder="Correo electrónico">
        <input id="password" type="password" class="form-control mb-2" placeholder="Contraseña">
        <div id="loginMsg" class="text-danger small"></div>
      </div>
      <div class="modal-footer">
        <button id="loginBtn" class="btn btn-primary w-100">Entrar</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= TOAST ================= -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="toast" class="toast">
    <div class="toast-header">
      <strong class="me-auto">Supermercado</strong>
      <button class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body" id="toastBody"></div>
  </div>
</div>

<!-- ================= JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let page = 1;
let loading = false;

/* ================= PROMOCIONES (CUPONES ADMIN) ================= */
async function loadPromos(){
  const res = await fetch('/Supermercado/backend/public/api.php?action=promotions');
  const json = await res.json();
  promoSlides.innerHTML = '';

  if(!json.promos || json.promos.length === 0) return;

  json.promos.forEach((p,i)=>{
    promoSlides.innerHTML += `
      <div class="carousel-item ${i===0?'active':''}">
        <div class="promo-slide">
          <h3>${p.code}</h3>
          <p>
            ${p.type === 'percent'
              ? p.value + '% de descuento'
              : '$' + p.value + ' de descuento'}
          </p>
        </div>
      </div>
    `;
  });
}

/* ================= PRODUCTOS (ADMIN → FRONTEND) ================= */
async function loadProducts(reset=false){
  if(loading) return;
  loading = true;
  loader.classList.remove('d-none');

  if(reset){
    page = 1;
    products.innerHTML = '';
  }

  const search = search.value;
  const res = await fetch(`/Supermercado/backend/public/api.php?action=products_public&page=${page}&search=${search}`);
  const json = await res.json();

  loader.classList.add('d-none');
  loading = false;

  if(json.products.length === 0 && page === 1){
    emptyMsg.classList.remove('d-none');
    return;
  }

  emptyMsg.classList.add('d-none');

  json.products.forEach(p=>{
    products.innerHTML += `
      <div class="col-md-3">
        <div class="product-card">
          <img src="/Supermercado/uploads/${p.image}" alt="${p.name}">
          <div class="product-info">
            <h6>${p.name}</h6>
            <div class="price">$${p.price}</div>
            <button class="btn btn-primary btn-sm w-100"
              ${p.stock <= 0 ? 'disabled' : ''}>
              ${p.stock > 0 ? 'Agregar al carrito' : 'Sin stock'}
            </button>
          </div>
        </div>
      </div>
    `;
  });

  page++;
  loadMoreContainer.classList.remove('d-none');
}

searchBtn.onclick = () => loadProducts(true);
loadMoreBtn.onclick = () => loadProducts();

loadPromos();
loadProducts();
</script>

</body>
</html>
