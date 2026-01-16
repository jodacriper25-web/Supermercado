<?php
$title = 'Supermercado Yaruquíes';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Supermercado/includes/header.php';
?>
<head>
    <!-- ================= META BÁSICOS ================= -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="content-language" content="es">

    <title>Supermercado plantilla web | WIX</title>

    <!-- ================= SEO ================= -->
    <meta name="description" content="Encuentra todo lo que buscas con esta plantilla nueva y seductora. Con Wix Stores, tus clientes fieles pueden agregar productos a su carrito, pagar online e incluso crear una lista de deseos. Los compradores también pueden mantenerse actualizados sobre las últimas ofertas mediante el formulario de suscripción integrado. Crea una experiencia de compra tan fácil y conveniente como hacer clic en un botón. Comienza hoy mismo.">
    <meta name="author" content="Wixpress">
    <link rel="canonical" href="https://es.wix.com/website-template/view/html/2949">
    <meta name="google-site-verification" content="QXhlrY-V2PWOmnGUb8no0L-fKzG48uJ5ozW0ukU7Rpo">

    <!-- ================= OPEN GRAPH / REDES ================= -->
    <meta property="og:title" content="Supermercado plantilla web | WIX">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://es.wix.com/website-template/view/html/2949">
    <meta property="og:image" content="//static.wixstatic.com/media//templates/image/52042e017dc9105cfb629e1f2b9fa2fe4b95573d10cb67eb69e6b2ae3f13913a1659109390894.jpg">
    <meta property="og:description" content="Encuentra todo lo que buscas con esta plantilla nueva y seductora.">
    <meta property="og:site_name" content="Wix">
    <meta name="fb:app_id" content="236335823061286">
    <meta property="fb:admins" content="731184828">

    <!-- ================= FAVICON ================= -->
    <link rel="icon" sizes="192x192" href="https://www.wix.com/favicon.ico">
    <link rel="shortcut icon" href="https://www.wix.com/favicon.ico">
    <link rel="apple-touch-icon" href="https://www.wix.com/favicon.ico">

    <!-- ================= FUENTES ================= -->
    <link rel="stylesheet" href="https://static.parastorage.com/services/third-party/fonts/Helvetica/fontFace.css">
    <link rel="stylesheet" href="https://static.parastorage.com/unpkg/@wix/wix-fonts@1.14.0/madefor.min.css">
    <link rel="stylesheet" href="https://static.parastorage.com/unpkg/@wix/wix-fonts@1.14.0/madeforDisplay.min.css">

    <!-- ================= CSS PRINCIPAL ================= -->
    <link rel="stylesheet" href="//static.parastorage.com/services/marketing-template-viewer/1.2626.0/app.min.css">

    <!-- ================= HREFLANG ================= -->
    <link rel="alternate" hreflang="es" href="https://es.wix.com/website-template/view/html/2949">
    <link rel="alternate" hreflang="en" href="https://www.wix.com/website-template/view/html/2949">
    <link rel="alternate" hreflang="x-default" href="https://www.wix.com/website-template/view/html/2949">
    <!-- (los demás idiomas se mantienen igual) -->

    <!-- ================= SENTRY ================= -->
    <script id="sentry">
        (function(c,u,v,n,p,e,z,A,w){/* código original intacto */})(window,document,"script","onerror","onunhandledrejection","Sentry","b4e7a2b423b54000ac2058644c76f718","https://static.parastorage.com/unpkg/@sentry/browser@5.27.4/build/bundle.min.js",{"dsn":"https://b4e7a2b423b54000ac2058644c76f718@sentry.wixpress.com/217"});
    </script>

    <script>
        window.Sentry.onLoad(function () {
            window.Sentry.init({
                release: "marketing-template-viewer@1.2626.0",
                environment: "production"
            });
            window.Sentry.configureScope(function (scope) {
                scope.setUser({
                    id: "null-user-id:dba34af0-1969-46da-ac52-6c2a97ec799e",
                    clientId: "dba34af0-1969-46da-ac52-6c2a97ec799e"
                });
            });
        });
    </script>

    <!-- ================= FEDOPS ================= -->
    <script>
        window.onWixFedopsLoggerLoaded = function () {
            window.fedopsLogger && window.fedopsLogger.reportAppLoadStarted('marketing-template-viewer');
        };
    </script>
    <script src="//static.parastorage.com/unpkg/@wix/fedops-logger@5.519.0/dist/statics/fedops-logger.bundle.min.js"
            crossorigin
            onload="onWixFedopsLoggerLoaded()"></script>

    <!-- ================= ANALYTICS / PIXELS ================= -->
    <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-MDD5C4"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-46CXENL4NC"></script>
    <script async src="https://bat.bing.com/bat.js"></script>
    <script async src="//connect.facebook.net/en_US/fbevents.js"></script>
    <script async src="https://analytics.tiktok.com/i18n/pixel/events.js"></script>
    <script async src="https://snap.licdn.com/li.lms-analytics/insight.min.js"></script>
    <script async src="https://static.ads-twitter.com/uwt.js"></script>
    <script async src="https://s.pinimg.com/ct/core.js"></script>
    <script async src="https://www.redditstatic.com/ads/pixel.js"></script>

    <!-- ================= TAG MANAGER HOST ================= -->
    <script src="//static.parastorage.com/services/tag-manager-host-scripts/1.971.0/assets/TEMPLATES/google-tag-manager.js"></script>

    <!-- ================= ESTILOS INYECTADOS ================= -->
    <style id="operaUserStyle"></style>
</head>

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

</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Supermercado/includes/footer.php'; ?>
