<?php $title = 'Supermercado Yaruquíes'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php';

<div id="promoCarouselContainer" class="container mt-3">
  <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner" id="promoSlides"></div>
    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>
    </button>
  </div>
</div>

<main class="container mt-4">
  <div class="row mb-3">
    <div class="col-12 d-flex">
      <input id="search" class="form-control me-2" placeholder="Buscar productos...">
      <button id="searchBtn" class="btn btn-primary">Buscar</button>
      <div id="loader" class="spinner-border text-primary ms-3" role="status" style="display:none"><span class="visually-hidden">Loading...</span></div>
    </div>
  </div>
  <div id="products" class="row"></div>
  <div id="loadMoreContainer" class="text-center my-3" style="display:none"><button id="loadMoreBtn" class="btn btn-outline-primary">Cargar más</button></div>
</main>

<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Carrito</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="cartBody"></div>
      <div class="modal-footer">
        <button id="checkoutBtn" class="btn btn-success">Pagar</button>
      </div>
    </div>
  </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Iniciar sesión</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><input id="email" class="form-control" placeholder="Email"></div>
        <div class="mb-3"><input id="password" type="password" class="form-control" placeholder="Contraseña"></div>
        <div id="loginMsg" class="text-danger"></div>
      </div>
      <div class="modal-footer"><button id="loginBtn" class="btn btn-primary">Entrar</button></div>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:11">
  <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Notificación</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toast-body"></div>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/footer.php'; ?>