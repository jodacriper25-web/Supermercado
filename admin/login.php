<?php
// Admin Login
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | Supermercado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm p-4" style="width:380px">
  <h4 class="mb-3 text-center">🔐 Admin Login</h4>

  <input id="email" class="form-control mb-2" placeholder="Email">
  <input id="password" type="password" class="form-control mb-3" placeholder="Contraseña">

  <div id="msg" class="text-danger small mb-2"></div>

  <button id="loginBtn" class="btn btn-primary w-100">Entrar</button>
</div>

<script>
document.getElementById('loginBtn').addEventListener('click', async () => {

  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const msg = document.getElementById('msg');

  msg.innerText = '';

  if (!email || !password) {
    msg.innerText = 'Completa todos los campos';
    return;
  }

  const res = await fetch('/Supermercado/backend/public/api.php?action=admin_login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });

  const json = await res.json();

  if (json.success) {
    localStorage.setItem('admin_csrf', json.csrf);
    window.location.href = 'dashboard.php';
  } else {
    msg.innerText = json.error || 'Credenciales inválidas';
  }
});
</script>


</body>
</html>
