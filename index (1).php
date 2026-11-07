<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="mb-0">Categorías</h1>
  <a class="btn btn-acento" href="<?= url('categoria/crear') ?>">Nueva categoría</a>
</div>
<div class="table-responsive">
<table class="table table-hover">
  <thead><tr><th>Nombre</th><th>Productos</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
  <?php foreach (($categorias ?? []) as $c): ?>
    <tr>
      <td><?= e($c['nombre']) ?></td>
      <td><?= (int)$c['productos'] ?></td>
      <td class="text-end">
        <a class="btn btn-outline-light btn-sm" href="<?= url('categoria/editar', ['id' => $c['id']]) ?>">Editar</a>
        <form class="d-inline" method="post" action="<?= url('categoria/eliminar') ?>"
              data-confirm="¿Eliminar la categoría «<?= e($c['nombre']) ?>»?">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>">
          <button class="btn btn-peligro btn-sm">Eliminar</button>
        </form>
        
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
