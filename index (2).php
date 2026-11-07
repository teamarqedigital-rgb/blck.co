<?php $admin = $_SESSION['usuario']['rol'] === 'administrador'; ?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
  <h1 class="mb-0">Productos</h1>
  <?php if ($admin): ?><a class="btn btn-acento" href="<?= url('producto/crear') ?>">Nuevo producto</a><?php endif; ?>
</div>
<form class="mb-3 d-flex gap-2" method="get" action="<?= url() ?>">
  <input type="hidden" name="r" value="producto/index">
  <input class="form-control" style="max-width:320px" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre" aria-label="Buscar producto">
  <button class="btn btn-outline-light">Buscar</button>
</form>
<div class="table-responsive">
<table class="table table-hover">
  <thead><tr><th>Producto</th><th>Categoría</th><th class="text-end">Precio</th><th class="text-end">Stock</th><th>Estado</th><?php if ($admin): ?><th class="text-end">Acciones</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach (($productos ?? []) as $p): ?>
    <tr>
      <td><?= e($p['nombre']) ?></td>
      <td class="text-secondary"><?= e($p['categoria']) ?></td>
      <td class="text-end"><?= money($p['precio']) ?></td>
      <td class="text-end" <?= $p['stock'] <= STOCK_BAJO ? 'style="color:var(--peligro)"' : '' ?>><?= (int)$p['stock'] ?></td>
      <td><?= $p['activo'] ? 'Activo' : '<span class="text-secondary">Inactivo</span>' ?></td>
      <?php if ($admin): ?>
      <td class="text-end">
        <a class="btn btn-outline-light btn-sm" href="<?= url('producto/editar', ['id' => $p['id']]) ?>">Editar</a>
        <form class="d-inline" method="post" action="<?= url('producto/eliminar') ?>"
              data-confirm="¿Eliminar «<?= e($p['nombre']) ?>»? Si tiene ventas, solo se desactivará.">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="btn btn-peligro btn-sm">Eliminar</button>
        </form>
      </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  <?php if (!$productos): ?><tr><td colspan="6" class="text-secondary">No hay productos que coincidan con la búsqueda.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
