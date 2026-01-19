<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
  <h3>🔐 Iniciar sesión</h3>

  <input id="email" placeholder="Correo electrónico">
  <input id="password" type="password" placeholder="Contraseña">

  <div id="loginMsg"></div>

  <button id="loginBtn">Entrar</button>

  <div class="auth-links">
    <a href="forgot.php">¿Olvidaste tu contraseña?</a>
    <a href="register.php">Crear cuenta</a>
  </div>
</div>

<script>
loginBtn.onclick = async () => {
  const res = await fetch('/Supermercado/backend/public/api.php?action=login', {
    method:'POST',
    body: JSON.stringify({
      email: email.value,
      password: password.value
    })
  });

  const json = await res.json();

  if(json.success){
    location.href = 'index.php';
  } else {
    loginMsg.innerText = json.error;
  }
};
</script>
</body>
</html>
