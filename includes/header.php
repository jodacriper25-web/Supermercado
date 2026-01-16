<?php
// Shared header include - outputs <head> and nav. Set $title before including if desired.
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo isset($title) ? htmlspecialchars($title) : 'Supermercado Yaruquíes'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/Supermercado/frontend/public/assets/css/styles.css" rel="stylesheet">
  <link href="/Supermercado/frontend/public/assets/css/admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/Supermercado/">Supermercado Yaruquíes</a>
    <div class="d-flex">
      <button id="view-cart" class="btn btn-outline-primary">Carrito <span id="cart-count" class="badge bg-secondary">0</span></button>
    </div>
  </div>
</nav>
<div class="container container-main mt-3">
