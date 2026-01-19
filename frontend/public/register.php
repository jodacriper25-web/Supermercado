<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro | Supermercado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm p-4 login-card">
  <h4 class="text-center mb-3">📝 Crear cuenta</h4>

  <input id="name" class="form-control mb-2" placeholder="Nombre completo">
  <input id="email" class="form-control mb-2" placeholder="Correo electrónico">
  <input id="password" type="password" class="form-control mb-2" placeholder="Contraseña">

  <div id="msg" class="text-danger small mb-2"></div>

  <button id="registerBtn" class="btn btn-success w-100">
    Registrarse
  </button>
</div>

<script>
registerBtn.onclick = async () => {
  msg.innerText = '';

  const body = {
    name: name.value,
    email: email.value,
    password: password.value
  };

  const res = await fetch('/Supermercado/backend/public/api.php?action=client_register', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(body)
  });

  const json = await res.json();

  if(json.success){
    window.location.href='login.php';
  }else{
    msg.innerText = json.error || 'Error al crear cuenta';
  }
};
</script>

</body>
</html>
