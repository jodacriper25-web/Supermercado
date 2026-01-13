<?php
$title = 'Supermercado Yaruquíes';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Supermercado/includes/header.php';
?>

<!-- ================= PROMOCIONES ================= -->
<section id="promoCarouselContainer" class="container mt-3">
  <div id="promoCarousel" class="carousel slide shadow-sm rounded" data-bs-ride="carousel">
    
    <div class="carousel-inner" id="promoSlides">
      <!-- Slides dinámicos -->
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
      <span class="visually-hidden">Anterior</span>
    </button>

    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>

  </div>
</section>

<!-- ================= CONTENIDO PRINCIPAL ================= -->
<main class="container mt-4">

  <!-- BUSCADOR -->
  <section class="row mb-4">
    <div class="col-12 d-flex align-items-center gap-2">
      <input 
        id="search" 
        type="search" 
        class="form-control" 
        placeholder="Buscar productos..." 
        aria-label="Buscar productos"
      >

      <button id="searchBtn" class="btn btn-primary">
        <i class="bi bi-search"></i> Buscar
      </button>

      <div id="loader" class="spinner-border text-primary" role="status" style="display:none">
        <span class="visually-hidden">Cargando...</span>
      </div>
    </div>
  </section>

  <!-- PRODUCTOS -->
  <section id="products" class="row g-3">
    <!-- Productos dinámicos -->
  </section>

  <!-- MENSAJE VACÍO -->
  <div id="emptyMsg" class="text-center text-muted my-4" style="display:none">
    No se encontraron productos 😕
  </div>

  <!-- CARGAR MÁS -->
  <div id="loadMoreContainer" class="text-center my-4" style="display:none">
    <button id="loadMoreBtn" class="btn btn-outline-primary">
      Cargar más productos
    </button>
  </div>

</main>

<!-- ================= MODAL CARRITO ================= -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">🛒 Carrito de compras</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="cartBody">
        <!-- Items del carrito -->
      </div>

      <div class="modal-footer">
        <button id="checkoutBtn" class="btn btn-success w-100">
          Proceder al pago
        </button>
      </div>

    </div>
  </div>
</div>

<!-- ================= MODAL LOGIN ================= -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">🔐 Iniciar sesión</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <input id="email" type="email" class="form-control" placeholder="Correo electrónico">
        </div>

        <div class="mb-3">
          <input id="password" type="password" class="form-control" placeholder="Contraseña">
        </div>

        <div id="loginMsg" class="text-danger small"></div>
      </div>

      <div class="modal-footer">
        <button id="loginBtn" class="btn btn-primary w-100">
          Entrar
        </button>
      </div>

    </div>
  </div>
</div>

<!-- ================= TOAST ================= -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
  <div id="toast" class="toast" role="alert">
    <div class="toast-header">
      <strong class="me-auto">Supermercado</strong>
      <button class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body" id="toast-body"></div>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Supermercado/includes/footer.php'; ?>
