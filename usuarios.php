<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h1 class="mb-1">Usuarios del equipo</h1>
    <p class="text-secondary mb-0">Administra el acceso y los roles del personal.</p>
  </div>
  <a class="btn btn-acento" href="<?= url('usuario/crear') ?>">Nueva cuenta</a>
</div>

<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th class="text-end">Acción</th></tr>
    </thead>
    <tbody>
      <?php foreach ($usuarios as $usuario): ?>
        <tr>
          <td><?= e($usuario['nombre']) ?><?php if ((int)$usuario['id'] === (int)$_SESSION['usuario']['id']): ?> <span class="text-secondary small">(tú)</span><?php endif; ?></td>
          <td><?= e($usuario['correo']) ?></td>
          <td><?= e(ucfirst($usuario['rol'])) ?></td>
          <td>
            <span class="estado estado-<?= (int)$usuario['activo'] === 1 ? 'activo' : 'inactivo' ?>">
              <?= (int)$usuario['activo'] === 1 ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td class="text-end">
            <a class="btn btn-outline-light btn-sm" href="<?= url('usuario/editar', ['id' => $usuario['id']]) ?>">Administrar</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$usuarios): ?>
        <tr><td colspan="5" class="text-secondary">No hay cuentas de equipo para mostrar.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<p class="text-secondary small mt-3 mb-0">Las cuentas se desactivan en lugar de eliminarse para conservar la trazabilidad de los pedidos.</p>
