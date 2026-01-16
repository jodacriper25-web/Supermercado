<?php
// Shared footer include - scripts and closing tags
?>
</div> <!-- /.container-main -->
<footer class="footer mt-4 py-3 bg-light">
  <div class="container text-center text-muted small">© <?php echo date('Y'); ?> Supermercado Yaruquíes</div>
</footer>

<!-- Toast container shared -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:11">
  <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Notificación</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toast-body"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Supermercado/frontend/public/assets/js/main.js"></script>
</body>
</html>
