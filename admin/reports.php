<?php
// Reportes (admin)
?>
<?php $title = 'Admin - Reportes'; require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container mt-4">
  <h1>Reportes de ventas</h1>
  <div class="d-flex gap-2 mb-3">
    <input id="start" type="date" class="form-control" style="max-width:200px">
    <input id="end" type="date" class="form-control" style="max-width:200px">
    <button id="loadBtn" class="btn btn-primary">Cargar</button>
    <a id="csvLink" class="btn btn-outline-secondary" href="#">Exportar CSV</a>
    <a id="pdfLink" class="btn btn-outline-secondary" href="#">Exportar PDF</a>
  </div>
  <canvas id="chart"></canvas>
</div>
<script>
function csvUrl(start,end){ return '/Supermercado/backend/public/api.php?action=reports&start='+start+'&end='+end+'&format=csv'; }

document.getElementById('loadBtn').addEventListener('click', async ()=>{
  const start = document.getElementById('start').value; const end = document.getElementById('end').value;
  document.getElementById('csvLink').href = csvUrl(start,end);
  const res = await fetch('/Supermercado/backend/public/api.php?action=reports&start='+start+'&end='+end);
  const json = await res.json(); const labels = json.report.map(r=>r.date); const data = json.report.map(r=>parseFloat(r.total));
  document.getElementById('csvLink').href = '/Supermercado/backend/public/api.php?action=reports&start='+start+'&end='+end+'&format=csv';
  document.getElementById('pdfLink').href = '/Supermercado/backend/public/api.php?action=reports&start='+start+'&end='+end+'&format=pdf';
  const ctx = document.getElementById('chart'); if (window.myChart) window.myChart.destroy(); window.myChart = new Chart(ctx, {type:'line', data:{labels, datasets:[{label:'Ventas', data, borderColor:'#007bff', fill:false}]}});
});
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/Supermercado/includes/footer.php'; ?>