<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
  <h3>🔁 Recuperar contraseña</h3>

  <input id="email" placeholder="Correo electrónico">

  <div id="msg"></div>

  <button id="sendBtn">Enviar enlace</button>

  <div class="auth-links">
    <a href="login.php">Volver</a>
  </div>
</div>

<script>
sendBtn.onclick = async () => {
  const res = await fetch('/Supermercado/backend/public/api.php?action=forgot', {
    method:'POST',
    body: JSON.stringify({ email: email.value })
  });

  const json = await res.json();
  msg.innerText = json.message;
};
</script>

</body>
</html>
