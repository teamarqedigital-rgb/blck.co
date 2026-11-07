<?php $abierto = isset(Pedido::SIGUIENTE[$p['estado']]); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h1 class="mb-0">Pedido #<?= (int)$p['id'] ?></h1>
  <span class="estado estado-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <p class="text-secondary">
      Cliente: <span class="text-body"><?= e($p['cliente']) ?></span> ·
      Atendió: <span class="text-body"><?= e($p['atendio']) ?></span> ·
      <?= e(date('d/m/Y H:i', strtotime($p['fecha']))) ?>
    </p>
    <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Producto</th><th class="text-end">Cant.</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($detalle as $d): ?>
        <tr><td><?= e($d['nombre']) ?></td><td class="text-end"><?= (int)$d['cantidad'] ?></td>
            <td class="text-end"><?= money($d['precio_unitario']) ?></td><td class="text-end"><?= money($d['subtotal']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= money($p['total']) ?></th></tr></tfoot>
    </table>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="panel mb-3">
      <h2 class="h6">Pago</h2>
      <?php if ($pago): ?>
        <p class="mb-0">Pagado con <?= e($pago['metodo']) ?> · <?= money($pago['monto']) ?></p>
      <?php elseif ($p['estado'] !== 'cancelado'): ?>
        <form method="post" action="<?= url('pedido/pagar') ?>" class="d-flex gap-2">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <select class="form-select" name="metodo" aria-label="Método de pago" required>
            <option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="transferencia">Transferencia</option>
          </select>
          <button class="btn btn-acento">Cobrar</button>
        </form>
      <?php else: ?><p class="text-secondary mb-0">Pedido cancelado.</p><?php endif; ?>
    </div>
    <?php if ($abierto): ?>
    <div class="panel d-grid gap-2">
      <form method="post" action="<?= url('pedido/avanzar') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
        <button class="btn btn-acento w-100">Pasar a «<?= e(Pedido::SIGUIENTE[$p['estado']]) ?>»</button>
      </form>
      <?php if (!$pago): ?>
      <form method="post" action="<?= url('pedido/cancelar') ?>" data-confirm="¿Cancelar el pedido #<?= (int)$p['id'] ?>? El stock volverá al inventario.">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
        <button class="btn btn-peligro w-100">Cancelar pedido</button>
      </form>
      <?php endif; ?>
    </div>
    <?php else: ?>
      <p class="text-secondary">Este pedido ya no se puede modificar.</p>
    <?php endif; ?>
  </div>
</div>
<a class="btn btn-outline-light mt-4" href="<?= url('pedido/index') ?>">Volver a pedidos</a>
