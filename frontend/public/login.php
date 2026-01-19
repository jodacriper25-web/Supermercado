<?php
session_start();

/* Si ya está logueado como cliente, volver al inicio */
if (isset($_SESSION['client'])) {
  header('Location: /Supermercado/frontend/public/index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar sesión | Supermercado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm p-4 login-card">
  <h4 class="text-center mb-3">🔐 Iniciar sesión</h4>

  <input id="email" class="form-control mb-2" placeholder="Correo electrónico">
  <input id="password" type="password" class="form-control mb-2" placeholder="Contraseña">

  <div id="msg" class="text-danger small mb-2"></div>

  <button id="loginBtn" class="btn btn-primary w-100 mb-2">
    Entrar
  </button>

  <div class="text-center small">
    <a href="forgot.php">¿Olvidaste tu contraseña?</a><br>
    <a href="register.php">Crear una cuenta</a>
  </div>
</div>

<script>
loginBtn.onclick = async () => {
  msg.innerText = '';

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value.trim();

  if (!email || !password) {
    msg.innerText = 'Completa todos los campos';
    return;
  }

  const res = await fetch('/Supermercado/backend/public/api.php?action=client_login', {
    method: 'POST',
    headers: { 'Content-Type':'application/json' },
    body: JSON.stringify({ email, password })
  });

  const json = await res.json();

  if (json.success) {
    window.location.href = 'index.php';
  } else {
    msg.innerText = json.error || 'Credenciales inválidas';
  }
};
</script>

</body>
</html>
