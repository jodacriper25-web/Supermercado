<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear cuenta</title>
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
  <h3>📝 Crear cuenta</h3>

  <input id="name" placeholder="Nombre completo">
  <input id="email" placeholder="Correo electrónico">
  <input id="password" type="password" placeholder="Contraseña">

  <div id="msg"></div>

  <button id="registerBtn">Registrarme</button>

  <div class="auth-links">
    <a href="login.php">Ya tengo cuenta</a>
  </div>
</div>

<script>
registerBtn.onclick = async () => {
  const res = await fetch('/Supermercado/backend/public/api.php?action=register', {
    method:'POST',
    body: JSON.stringify({
      name: name.value,
      email: email.value,
      password: password.value
    })
  });

  const json = await res.json();
  msg.innerText = json.success ? 'Cuenta creada ✔️' : json.error;
};
</script>

</body>
</html>
