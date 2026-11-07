<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="mb-0">Pedidos</h1>
  <a class="btn btn-acento" href="<?= url('pedido/crear') ?>">Nuevo pedido</a>
</div>
<div class="mb-3 d-flex flex-wrap gap-2">
  <?php foreach (['' => 'Todos', 'pendiente' => 'Pendientes', 'preparando' => 'En preparación', 'entregado' => 'Entregados', 'cancelado' => 'Cancelados'] as $k => $txt): ?>
    <a class="btn btn-sm <?= $estado === $k ? 'btn-acento' : 'btn-outline-light' ?>"
       href="<?= url('pedido/index', $k ? ['estado' => $k] : []) ?>"><?= $txt ?></a>
  <?php endforeach; ?>
</div>
<div class="table-responsive">
<table class="table table-hover">
  <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Atendió</th><th>Estado</th><th>Pago</th><th class="text-end">Total</th></tr></thead>
  <tbody>
  <?php foreach ($pedidos as $p): ?>
    <tr>
      <td><a href="<?= url('pedido/ver', ['id' => $p['id']]) ?>"><?= $p['id'] ?></a></td>
      <td class="text-secondary"><?= e(date('d/m/Y H:i', strtotime($p['fecha']))) ?></td>
      <td><?= e($p['cliente']) ?></td>
      <td class="text-secondary"><?= e($p['atendio']) ?></td>
      <td><span class="estado estado-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span></td>
      <td><?= $p['pagado'] ? 'Pagado' : '<span class="text-secondary">Sin pago</span>' ?></td>
      <td class="text-end"><?= money($p['total']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$pedidos): ?><tr><td colspan="7" class="text-secondary">No hay pedidos en este estado.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
