<?php
$campo = fn($n, $d = '') => e($item[$n] ?? $d);
$inv = fn($n) => isset($errores[$n]) ? 'is-invalid' : '';
?>
<h1 class="mb-4"><?= e($titulo) ?></h1>
<form method="post" class="needs-validation panel" style="max-width:620px" novalidate>
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label" for="nombre">Nombre</label>
    <input class="form-control <?= $inv('nombre') ?>" id="nombre" name="nombre" required minlength="3" maxlength="80" value="<?= $campo('nombre') ?>">
    <div class="invalid-feedback"><?= e($errores['nombre'] ?? 'El nombre debe tener entre 3 y 80 caracteres.') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="descripcion">Descripción</label>
    <input class="form-control" id="descripcion" name="descripcion" maxlength="255" value="<?= $campo('descripcion') ?>">
  </div>
  <div class="row g-3 mb-3">
    <div class="col-sm-4">
      <label class="form-label" for="precio">Precio (COP)</label>
      <input class="form-control <?= $inv('precio') ?>" id="precio" name="precio" type="number" min="1" step="50" required value="<?= $campo('precio') ?>">
      <div class="invalid-feedback"><?= e($errores['precio'] ?? 'El precio debe ser mayor que 0.') ?></div>
    </div>
    <div class="col-sm-4">
      <label class="form-label" for="stock">Stock</label>
      <input class="form-control <?= $inv('stock') ?>" id="stock" name="stock" type="number" min="0" step="1" required value="<?= $campo('stock', 0) ?>">
      <div class="invalid-feedback"><?= e($errores['stock'] ?? 'El stock debe ser 0 o más.') ?></div>
    </div>
    <div class="col-sm-4">
      <label class="form-label" for="categoria_id">Categoría</label>
      <select class="form-select <?= $inv('categoria_id') ?>" id="categoria_id" name="categoria_id" required>
        <option value="">Elige una</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($item['categoria_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="invalid-feedback"><?= e($errores['categoria_id'] ?? 'Elige una categoría.') ?></div>
    </div>
  </div>
  <div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="activo" name="activo" <?= ($item['activo'] ?? 1) ? 'checked' : '' ?>>
    <label class="form-check-label" for="activo">Visible en el menú y disponible para vender</label>
  </div>
  <button class="btn btn-acento" type="submit">Guardar producto</button>
  <a class="btn btn-outline-light" href="<?= url('producto/index') ?>">Cancelar</a>
</form>
