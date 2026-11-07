<?php
$campo = fn($n) => e($item[$n] ?? '');
$inv = fn($n) => isset($errores[$n]) ? 'is-invalid' : '';
?>
<h1 class="mb-4"><?= e($titulo) ?></h1>
<form method="post" class="needs-validation panel" style="max-width:520px" novalidate>
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label" for="nombre">Nombre</label>
    <input class="form-control <?= $inv('nombre') ?>" id="nombre" name="nombre" required minlength="3" maxlength="80" value="<?= $campo('nombre') ?>">
    <div class="invalid-feedback"><?= e($errores['nombre'] ?? 'El nombre debe tener entre 3 y 80 caracteres.') ?></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="telefono">Teléfono (opcional)</label>
    <input class="form-control <?= $inv('telefono') ?>" id="telefono" name="telefono" pattern="[0-9]{7,15}" inputmode="numeric" value="<?= $campo('telefono') ?>">
    <div class="invalid-feedback"><?= e($errores['telefono'] ?? 'Solo números, de 7 a 15 dígitos.') ?></div>
  </div>
  <div class="mb-4">
    <label class="form-label" for="correo">Correo (opcional)</label>
    <input class="form-control <?= $inv('correo') ?>" id="correo" name="correo" type="email" value="<?= $campo('correo') ?>">
    <div class="invalid-feedback"><?= e($errores['correo'] ?? 'El correo no tiene un formato válido.') ?></div>
  </div>
  <button class="btn btn-acento" type="submit">Guardar cliente</button>
  <a class="btn btn-outline-light" href="<?= url('cliente/index') ?>">Cancelar</a>
</form>
