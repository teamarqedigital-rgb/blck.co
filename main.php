<?php
$u = $_SESSION['usuario'];
$ruta = $_GET['r'] ?? '';
$esAdmin = $u['rol'] === 'administrador';
$items = [
    ['dashboard/index', 'Panel', true],
    ['pedido/index', 'Pedidos', true],
    ['producto/index', 'Productos', true],
    ['categoria/index', 'Categorías', $esAdmin],
    ['cliente/index', 'Clientes', true],
    ['reporte/index', 'Reportes', $esAdmin],
    ['usuario/index', 'Usuarios', $esAdmin],
];
require __DIR__ . '/head.php';
?>
<body class="app-shell">
<header class="topbar d-lg-none">
  <a class="marca marca-imagen" href="<?= url('dashboard/index') ?>" aria-label="BLCK.CO — panel">
    <img src="<?= asset('blckco-header-logo.png') ?>" alt="BLCK.CO">
  </a>
  <button class="btn btn-outline-light btn-sm" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-label="Abrir menú">Menú</button>
</header>

<div class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="menuLateral" aria-label="Navegación principal">
  <div class="offcanvas-header d-lg-none">
    <span class="marca marca-imagen"><img src="<?= asset('blckco-header-logo.png') ?>" alt="BLCK.CO"></span>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#menuLateral" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body flex-column">
    <a class="marca marca-imagen d-none d-lg-block mb-4" href="<?= url('dashboard/index') ?>" aria-label="BLCK.CO — panel">
      <img src="<?= asset('blckco-header-logo.png') ?>" alt="BLCK.CO">
    </a>
    <nav class="nav flex-column mb-auto">
      <?php foreach ($items as [$r, $txt, $visible]): if (!$visible) continue; ?>
        <a class="nav-link <?= explode('/', $ruta)[0] === explode('/', $r)[0] ? 'activo' : '' ?>"
           href="<?= url($r) ?>"><?= $txt ?></a>
      <?php endforeach; ?>
      <a class="nav-link" href="<?= url('menu/index') ?>" target="_blank" rel="noopener">Ver menú público</a>
    </nav>
    <div class="usuario">
      <div class="fw-semibold"><?= e($u['nombre']) ?></div>
      <div class="text-secondary small mb-2"><?= e($u['rol']) ?></div>
      <form method="post" action="<?= url('auth/logout') ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline-light btn-sm w-100" type="submit">Cerrar sesión</button>
      </form>
    </div>
  </div>
</div>

<main class="contenido">
  <?php if ($f = flash()): ?>
    <div class="alert alert-<?= $f['tipo'] === 'ok' ? 'success' : 'danger' ?> alert-dismissible" role="alert">
      <?= e($f['msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>
  <?php require $archivo; ?>
</main>

<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-4">
        <h2 class="h5" id="modalTitulo">Confirmar acción</h2>
        <p class="text-secondary mb-4" id="modalTexto"></p>
        <div class="d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-peligro" id="modalOk">Confirmar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (!empty($scripts)) foreach ($scripts as $s): ?>
<script src="<?= asset($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
