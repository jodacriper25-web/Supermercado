<?php
// Página simple de login admin
?>
<?php $title = 'Admin Login - Supermercado'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php'; ?>
<div class="container container-main mt-5" style="max-width:420px">
  <h3>Iniciar sesión (admin)</h3>
  <div class="mb-3"><input id="email" class="form-control" placeholder="Email"></div>
  <div class="mb-3"><input id="password" type="password" class="form-control" placeholder="Contraseña"></div>
  <div id="msg" class="text-danger mb-3"></div>
  <button id="loginBtn" class="btn btn-primary">Entrar</button>
</div>
<script>
document.getElementById('loginBtn').addEventListener('click', async ()=>{
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const res = await fetch('/Supermercado/backend/public/api.php?action=admin_login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,password})});
  const json = await res.json();
  if (json.success){
    localStorage.setItem('admin_csrf', json.csrf||'');
    location.href='/Supermercado/admin/dashboard.php';
  } else { document.getElementById('msg').innerText='Credenciales inválidas'; }
});
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/footer.php'; ?>