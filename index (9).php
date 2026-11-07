<h1 class="mb-4">Panel</h1>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><div class="panel"><div class="cifra"><?= money($hoy['ingresos']) ?></div><div class="cifra-etiqueta">Ventas de hoy</div></div></div>
  <div class="col-6 col-lg-3"><div class="panel"><div class="cifra"><?= (int)$hoy['pedidos'] ?></div><div class="cifra-etiqueta">Pedidos de hoy</div></div></div>
  <div class="col-6 col-lg-3"><div class="panel"><div class="cifra"><?= (int)$hoy['pendientes'] ?></div><div class="cifra-etiqueta">En espera o preparación</div></div></div>
  <div class="col-6 col-lg-3"><div class="panel"><div class="cifra"><?= count($stockBajo) ?></div><div class="cifra-etiqueta">Productos con stock bajo</div></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h2 class="h5 mb-0">Pedidos recientes</h2>
      <a class="btn btn-acento btn-sm" href="<?= url('pedido/crear') ?>">Nuevo pedido</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>#</th><th>Cliente</th><th>Estado</th><th class="text-end">Total</th></tr></thead>
        <tbody>
        <?php foreach ($recientes as $p): ?>
          <tr>
            <td><a href="<?= url('pedido/ver', ['id' => $p['id']]) ?>"><?= $p['id'] ?></a></td>
            <td><?= e($p['cliente']) ?></td>
            <td><span class="estado estado-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span></td>
            <td class="text-end"><?= money($p['total']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recientes): ?><tr><td colspan="4" class="text-secondary">Aún no hay pedidos. Crea el primero.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-5">
    <h2 class="h5">Más vendidos</h2>
    <table class="table mb-4">
      <tbody>
      <?php foreach ($top as $t): ?>
        <tr><td><?= e($t['nombre']) ?></td><td class="text-end text-secondary"><?= (int)$t['vendidos'] ?> uds.</td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <h2 class="h5">Stock bajo</h2>
    <table class="table">
      <tbody>
      <?php foreach ($stockBajo as $s): ?>
        <tr><td><?= e($s['nombre']) ?></td><td class="text-end" style="color:var(--peligro)"><?= (int)$s['stock'] ?> uds.</td></tr>
      <?php endforeach; ?>
      <?php if (!$stockBajo): ?><tr><td class="text-secondary">Todo el inventario está por encima de <?= STOCK_BAJO ?> unidades.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
