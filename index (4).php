<?php $max = max(array_map(fn($f) => (float)$f['ingresos'], $porMes) ?: [1]); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="mb-0">Reportes</h1>
  <a class="btn btn-outline-light" href="<?= url('reporte/csv') ?>">Descargar ventas (CSV)</a>
</div>
<div class="row g-4">
  <div class="col-lg-7">
    <h2 class="h5">Ingresos por mes</h2>
    <table class="table">
      <thead><tr><th>Mes</th><th class="text-end">Pedidos</th><th style="width:40%">Ingresos</th></tr></thead>
      <tbody>
      <?php foreach ($porMes as $f): ?>
        <tr><td><?= e($f['mes']) ?></td><td class="text-end"><?= (int)$f['pedidos'] ?></td>
            <td><div style="background:var(--acento);height:6px;width:<?= round($f['ingresos'] / $max * 100) ?>%"></div>
                <small><?= money($f['ingresos']) ?></small></td></tr>
      <?php endforeach; ?>
      <?php if (!$porMes): ?><tr><td colspan="3" class="text-secondary">Aún no hay pedidos entregados.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <h2 class="h5 mt-4">Clientes frecuentes (3 o más pedidos entregados)</h2>
    <table class="table">
      <thead><tr><th>Cliente</th><th class="text-end">Pedidos</th><th class="text-end">Gastado</th></tr></thead>
      <tbody>
      <?php foreach ($frecuentes as $c): ?>
        <tr><td><?= e($c['nombre']) ?></td><td class="text-end"><?= (int)$c['pedidos'] ?></td><td class="text-end"><?= money($c['gastado']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$frecuentes): ?><tr><td colspan="3" class="text-secondary">Ningún cliente llega todavía a 3 pedidos.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="col-lg-5">
    <h2 class="h5">Productos más vendidos</h2>
    <table class="table mb-4">
      <thead><tr><th>Producto</th><th class="text-end">Uds.</th><th class="text-end">Ingresos</th></tr></thead>
      <tbody>
      <?php foreach ($top as $t): ?>
        <tr><td><?= e($t['nombre']) ?></td><td class="text-end"><?= (int)$t['vendidos'] ?></td><td class="text-end"><?= money($t['ingresos']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <h2 class="h5">Ventas por método de pago</h2>
    <table class="table mb-4">
      <tbody>
      <?php foreach ($metodos as $m): ?>
        <tr><td><?= e($m['metodo']) ?></td><td class="text-end text-secondary"><?= (int)$m['pagos'] ?> pagos</td><td class="text-end"><?= money($m['monto']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <h2 class="h5">Stock bajo</h2>
    <table class="table">
      <tbody>
      <?php foreach ($stockBajo as $s): ?>
        <tr><td><?= e($s['nombre']) ?></td><td class="text-end" style="color:var(--peligro)"><?= (int)$s['stock'] ?> uds.</td></tr>
      <?php endforeach; ?>
      <?php if (!$stockBajo): ?><tr><td class="text-secondary">Sin alertas de inventario.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
