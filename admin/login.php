<?php
// admin/login.php
session_start();

// Si ya está logueado como admin, redirigir
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
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
  <link rel="stylesheet" href="/Supermercado/frontend/public/css/admin.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm p-4" style="width:380px">
  <h4 class="mb-3 text-center">🔐 Admin Login</h4>

  <input id="email" type="email" class="form-control mb-2" placeholder="Email">
  <input id="password" type="password" class="form-control mb-3" placeholder="Contraseña">

  <div id="msg" class="text-danger small mb-2"></div>

  <button id="loginBtn" class="btn btn-primary w-100">Entrar</button>
</div>

<script>
document.getElementById('loginBtn').addEventListener('click', async () => {

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value.trim();
  const msg = document.getElementById('msg');

  msg.innerText = '';

  if (!email || !password) {
    msg.innerText = 'Completa todos los campos';
    return;
  }

  try {
    const res = await fetch('/Supermercado/backend/public/api.php?action=admin_login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    });

    // Si el backend responde HTML (error PHP), lo capturamos
    const text = await res.text();
    let json;

    try {
      json = JSON.parse(text);
    } catch (e) {
      console.error(text);
      msg.innerText = 'Error interno del servidor';
      return;
    }

    if (json.success) {
      // Guardar CSRF para peticiones admin
      localStorage.setItem('admin_csrf', json.csrf);

      // Redirigir al dashboard
      window.location.href = 'dashboard.php';
    } else {
      msg.innerText = json.error || 'Credenciales inválidas';
    }

  } catch (err) {
    console.error(err);
    msg.innerText = 'No se pudo conectar al servidor';
  }
});
</script>

</body>
</html>
