<?php
// Header compartido
// Definir $title antes de incluir este archivo si se desea un título personalizado
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= isset($title) ? htmlspecialchars($title) : 'Supermercado Yaruquíes'; ?></title>

  <!-- ================= BOOTSTRAP ================= -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <!-- ================= ICONOS ================= -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    rel="stylesheet"
  >

  <!-- ================= ESTILOS PROPIOS ================= -->
  <link rel="stylesheet" href="/Supermercado/frontend/public/assets/css/styles.css">
  <link rel="stylesheet" href="/Supermercado/frontend/public/assets/css/admin.css">

</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <div class="container">

    <!-- LOGO -->
    <a class="navbar-brand" href="/Supermercado/">
      Supermercado Yaruquíes
    </a>

    <!-- BOTÓN RESPONSIVE -->
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- CONTENIDO NAV -->
    <div class="collapse navbar-collapse" id="mainNavbar">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" href="/Supermercado/fronted/public/#">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Ofertas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contacto</a>
        </li>
      </ul>

      <!-- CARRITO -->
      <button id="view-cart" class="btn btn-outline-primary position-relative">
        <i class="bi bi-cart3"></i>
        Carrito
        <span
          id="cart-count"
          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
          0
        </span>
      </button>

    </div>
  </div>
</nav>

<!-- ================= CONTENEDOR PRINCIPAL ================= -->
