<?php
session_start();

if (!isset($_SESSION['client'])) {
  header('Location: login.php');
  exit;
}

$client = $_SESSION['client'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi perfil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/Supermercado/frontend/css/style.css">
</head>

<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">

      <div class="card shadow-sm p-4">
        <h4 class="mb-3">👤 Mi perfil</h4>

        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input id="name" class="form-control" value="<?= htmlspecialchars($client['name']) ?>">
        </div>

        <div class="mb-2">
          <label class="form-label">Correo</label>
          <input class="form-control" value="<?= htmlspecialchars($client['email']) ?>" disabled>
        </div>

        <div class="mb-2">
          <label class="form-label">Nueva contraseña</label>
          <input id="password" type="password" class="form-control" placeholder="Opcional">
        </div>

        <div id="msg" class="small mb-2"></div>

        <button id="saveBtn" class="btn btn-primary w-100">
          Guardar cambios
        </button>

        <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">
          Volver a la tienda
        </a>
      </div>

    </div>
  </div>
</div>

<script>
saveBtn.onclick = async () => {
  msg.innerText = '';

  const body = {
    name: name.value,
    password: password.value
  };

  const res = await fetch('/Supermercado/backend/public/api.php?action=client_update', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(body)
  });

  const json = await res.json();

  if(json.success){
    msg.className='text-success small';
    msg.innerText='Perfil actualizado';
  }else{
    msg.className='text-danger small';
    msg.innerText=json.error || 'Error';
  }
};
</script>

</body>
</html>
