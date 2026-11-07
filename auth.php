<?php
require __DIR__ . '/head.php';
$archivo = $archivo ?? null;
?>
<body class="auth">
  <main class="auth-caja">
    <?php if ($archivo !== null && is_file($archivo)) { require $archivo; } ?>
  </main>
</body>
</html>
