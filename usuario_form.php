<?php
$campo = static fn(string $nombre, $defecto = '') => e($item[$nombre] ?? $defecto);
$invalido = static fn(string $nombre) => isset($errores[$nombre]) ? 'is-invalid' : '';
?>
<h1 class="mb-4"><?= e($titulo) ?></h1>

<?php if (!empty($errores['general'])): ?>
  <div class="alert alert-danger" role="alert"><?= e($errores['general']) ?></div>
<?php endif; ?>

<form method="post" class="panel" style="max-width:620px">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label" for="nombre">Nombre</label>
    <input class="form-control <?= $invalido('nombre') ?>" id="nombre" name="nombre" required minlength="3" maxlength="80" autocomplete="name" value="<?= $campo('nombre') ?>">
    <?php if (isset($errores['nombre'])): ?><div class="invalid-feedback"><?= e($errores['nombre']) ?></div><?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="correo">Correo de acceso</label>
    <input class="form-control <?= $invalido('correo') ?>" id="correo" name="correo" type="email" required maxlength="100" autocomplete="email" value="<?= $campo('correo') ?>">
    <?php if (isset($errores['correo'])): ?><div class="invalid-feedback"><?= e($errores['correo']) ?></div><?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="rol_id">Rol</label>
    <select class="form-select <?= $invalido('rol_id') ?>" id="rol_id" name="rol_id" required <?= $esCuentaActual ? 'disabled' : '' ?>>
      <?php foreach ($roles as $rol): ?>
        <option value="<?= (int)$rol['id'] ?>" <?= (int)($item['rol_id'] ?? 0) === (int)$rol['id'] ? 'selected' : '' ?>><?= e(ucfirst($rol['nombre'])) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if ($esCuentaActual): ?><div class="form-text text-secondary">No puedes cambiar tu propio rol desde esta cuenta.</div><?php endif; ?>
    <?php if (isset($errores['rol_id'])): ?><div class="invalid-feedback"><?= e($errores['rol_id']) ?></div><?php endif; ?>
  </div>

  <?php if ($item): ?>
    <div class="form-check mb-3">
      <input type="hidden" name="activo" value="0">
      <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?= (int)($item['activo'] ?? 1) === 1 ? 'checked' : '' ?> <?= $esCuentaActual ? 'disabled' : '' ?>>
      <label class="form-check-label" for="activo">Cuenta activa</label>
      <?php if ($esCuentaActual): ?><div class="form-text text-secondary">No puedes desactivar tu propia cuenta.</div><?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label" for="clave"><?= $item ? 'Nueva contraseña (opcional)' : 'Contraseña' ?></label>
    <input class="form-control <?= $invalido('clave') ?>" id="clave" name="clave" type="password" <?= $item ? '' : 'required' ?> minlength="12" maxlength="72" autocomplete="new-password">
    <div class="form-text text-secondary">Usa entre 12 y 72 bytes. Al editar, déjalo vacío para conservar la contraseña actual.</div>
    <?php if (isset($errores['clave'])): ?><div class="invalid-feedback"><?= e($errores['clave']) ?></div><?php endif; ?>
  </div>

  <div class="mb-4">
    <label class="form-label" for="confirmar_clave">Confirmar contraseña</label>
    <input class="form-control <?= $invalido('confirmar_clave') ?>" id="confirmar_clave" name="confirmar_clave" type="password" <?= $item ? '' : 'required' ?> minlength="12" maxlength="72" autocomplete="new-password">
    <?php if (isset($errores['confirmar_clave'])): ?><div class="invalid-feedback"><?= e($errores['confirmar_clave']) ?></div><?php endif; ?>
  </div>

  <button class="btn btn-acento" type="submit">Guardar cuenta</button>
  <a class="btn btn-outline-light" href="<?= url('usuario/index') ?>">Cancelar</a>
</form>
