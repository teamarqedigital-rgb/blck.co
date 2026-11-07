<h1 class="mb-4"><?= e($titulo) ?></h1>
<form method="post" class="needs-validation panel" style="max-width:480px" novalidate>
  <?= csrf_field() ?>
  <div class="mb-4">
    <label class="form-label" for="nombre">Nombre</label>
    <input class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre"
           required minlength="3" maxlength="50" value="<?= e($item['nombre'] ?? '') ?>">
    <div class="invalid-feedback"><?= e($errores['nombre'] ?? 'El nombre debe tener entre 3 y 50 caracteres.') ?></div>
  </div>
  <button class="btn btn-acento" type="submit">Guardar categoría</button>
  <a class="btn btn-outline-light" href="<?= url('categoria/index') ?>">Cancelar</a>
</form>
