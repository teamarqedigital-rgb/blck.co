<?php $admin = $_SESSION['usuario']['rol'] === 'administrador'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="mb-0">Clientes</h1>
  <a class="btn btn-acento" href="<?= url('cliente/crear') ?>">Nuevo cliente</a>
</div>
<form class="mb-3 d-flex gap-2" method="get" action="<?= url() ?>">
  <input type="hidden" name="r" value="cliente/index">
  <input class="form-control" style="max-width:320px" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre" aria-label="Buscar cliente">
  <button class="btn btn-outline-light">Buscar</button>
</form>
<div class="table-responsive">
<table class="table table-hover">
  <thead><tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th class="text-end">Pedidos</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
  <?php foreach ($clientes as $c): ?>
    <tr>
      <td><?= e($c['nombre']) ?></td>
      <td class="text-secondary"><?= e($c['telefono'] ?? '—') ?></td>
      <td class="text-secondary"><?= e($c['correo'] ?? '—') ?></td>
      <td class="text-end"><?= (int)$c['pedidos'] ?></td>
      <td class="text-end">
        <a class="btn btn-outline-light btn-sm" href="<?= url('cliente/editar', ['id' => $c['id']]) ?>">Editar</a>
        <?php if ($admin): ?>
        <form class="d-inline" method="post" action="<?= url('cliente/eliminar') ?>"
              data-confirm="¿Eliminar a «<?= e($c['nombre']) ?>»?">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>">
          <button class="btn btn-peligro btn-sm">Eliminar</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$clientes): ?><tr><td colspan="5" class="text-secondary">No hay clientes que coincidan con la búsqueda.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
