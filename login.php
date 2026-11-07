<span class="marca">blck<span>.co</span></span>
<?php if ($error): ?>
  <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
<?php endif; ?>
<form method="post" class="needs-validation" novalidate>
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label" for="correo">Correo</label>
    <input class="form-control" type="email" id="correo" name="correo" required autofocus
           value="<?= e($_POST['correo'] ?? '') ?>" autocomplete="username">
    <div class="invalid-feedback">Escribe un correo válido.</div>
  </div>
  <div class="mb-4">
    <label class="form-label" for="clave">Contraseña</label>
    <input class="form-control" type="password" id="clave" name="clave" required autocomplete="current-password">
    <div class="invalid-feedback">Escribe tu contraseña.</div>
  </div>
  <button class="btn btn-acento w-100" type="submit">Ingresar</button>
</form>
<p class="mt-4 mb-0"><a href="<?= url('menu/index') ?>">Ver el menú</a></p>
