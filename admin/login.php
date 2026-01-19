<?php
// admin/login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado como admin, redirigir
if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | Supermercado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS Admin -->
  <link rel="stylesheet" href="/Supermercado/admin/admin.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow p-4 border-0" style="width:380px">
  <h4 class="mb-3 text-center fw-bold">🔐 Panel Administrativo</h4>
  <p class="text-center text-muted small mb-4">
    Acceso exclusivo para administradores
  </p>

  <div class="mb-2">
    <input id="email" type="email" class="form-control" placeholder="Correo electrónico" autocomplete="username">
  </div>

  <div class="mb-3">
    <input id="password" type="password" class="form-control" placeholder="Contraseña" autocomplete="current-password">
  </div>

  <div id="msg" class="text-danger small mb-3"></div>

  <button id="loginBtn" class="btn btn-primary w-100">
    Ingresar
  </button>

  <div class="text-center mt-3">
    <small class="text-muted">Supermercado · Admin</small>
  </div>
</div>

<script>
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const msg = document.getElementById('msg');

async function login() {

  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  msg.textContent = '';

  if (!email || !password) {
    msg.textContent = 'Completa todos los campos';
    return;
  }

  loginBtn.disabled = true;
  loginBtn.textContent = 'Verificando...';

  try {
    const res = await fetch('/Supermercado/backend/public/api.php?action=admin_login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });

    const text = await res.text();
    let data;

    try {
      data = JSON.parse(text);
    } catch {
      console.error(text);
      msg.textContent = 'Error interno del servidor';
      return;
    }

    if (data.success) {
      localStorage.setItem('admin_csrf', data.csrf);
      window.location.href = 'dashboard.php';
    } else {
      msg.textContent = data.error || 'Credenciales inválidas';
    }

  } catch (err) {
    console.error(err);
    msg.textContent = 'No se pudo conectar al servidor';
  } finally {
    loginBtn.disabled = false;
    loginBtn.textContent = 'Ingresar';
  }
}

// Click
loginBtn.addEventListener('click', login);

// Enter
passwordInput.addEventListener('keydown', e => {
  if (e.key === 'Enter') login();
});
</script>

</body>
</html>
