<h1 class="mb-4">Nuevo pedido</h1>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <div class="mb-4" style="max-width:380px">
    <label class="form-label" for="cliente_id">Cliente</label>
    <select class="form-select" id="cliente_id" name="cliente_id" required>
      <option value="">Elige un cliente</option>
      <?php foreach ($clientes as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($_POST['cliente_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="table-responsive">
  <table class="table">
    <thead><tr><th>Producto</th><th class="text-end">Precio</th><th class="text-end">Disponibles</th><th style="width:130px">Cantidad</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($productos as $p): ?>
      <tr data-precio="<?= e($p['precio']) ?>">
        <td><?= e($p['nombre']) ?></td>
        <td class="text-end"><?= money($p['precio']) ?></td>
        <td class="text-end text-secondary"><?= (int)$p['stock'] ?></td>
        <td><input class="form-control form-control-sm" type="number" min="0" max="<?= (int)$p['stock'] ?>"
                   name="cantidad[<?= $p['id'] ?>]" value="<?= (int)($_POST['cantidad'][$p['id']] ?? 0) ?: '' ?>"
                   aria-label="Cantidad de <?= e($p['nombre']) ?>"></td>
        <td class="text-end subtotal"></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <div class="panel d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div><span class="cifra-etiqueta"><span id="totalItems">0</span> productos</span><div class="cifra" id="totalPedido">$0</div></div>
    <div>
      <a class="btn btn-outline-light" href="<?= url('pedido/index') ?>">Cancelar</a>
      <button class="btn btn-acento" id="btnGuardar" type="submit" disabled>Registrar pedido</button>
    </div>
  </div>
</form>
