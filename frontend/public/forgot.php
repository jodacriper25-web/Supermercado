<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm p-4 login-card">
  <h4 class="text-center mb-3">🔑 Recuperar contraseña</h4>

  <p class="small text-muted text-center">
    Ingresa tu correo y te enviaremos instrucciones
  </p>

  <input id="email" class="form-control mb-2" placeholder="Correo electrónico">
  <div id="msg" class="text-danger small mb-2"></div>

  <button id="sendBtn" class="btn btn-primary w-100">
    Enviar enlace
  </button>

  <div class="text-center mt-2">
    <a href="login.php" class="small">Volver a iniciar sesión</a>
  </div>
</div>

<script>
sendBtn.onclick = async () => {
  msg.innerText = '';

  const email = document.getElementById('email').value.trim();
  if (!email) {
    msg.innerText = 'Ingresa tu correo';
    return;
  }

  const res = await fetch('/Supermercado/backend/public/api.php?action=client_forgot', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ email })
  });

  const json = await res.json();

  if (json.success) {
    msg.classList.remove('text-danger');
    msg.classList.add('text-success');
    msg.innerText = 'Revisa tu correo 📧';
  } else {
    msg.innerText = json.error || 'Correo no encontrado';
  }
};
</script>

</body>
</html>
