<?php require __DIR__ . '/head.php'; ?>
<body class="public-site">
  <header class="publico-top">
    <a class="marca marca-imagen" href="<?= url('menu/index') ?>" aria-label="BLCK.CO — inicio">
      <img src="<?= asset('blckco-header-logo.png') ?>" alt="BLCK.CO">
    </a>
    <nav class="publico-nav" aria-label="Navegación principal" data-i18n-aria="mainNavigation">
      <div class="publico-nav-links">
        <a href="#menu" data-i18n="navMenu">Menú</a>
        <a href="#para-llevar" data-i18n="navTakeaway">Para llevar</a>
        <a href="#nosotros" data-i18n="navAbout">Nosotros</a>
        <a href="#apparel" data-i18n="navApparel">Apparel</a>
        <a href="#ink-lab">Ink Lab</a>
        <a href="#dj-sessions">DJ Sessions</a>
        <a href="#morning-beats">Morning Beats</a>
        <a href="#visitanos" data-i18n="navVisit">Visítanos</a>
      </div>
      <div class="language-switch" role="group" aria-label="Language / Idioma">
        <button type="button" data-language="es" aria-pressed="true" aria-label="Ver en español">ES</button>
        <button type="button" data-language="en" aria-pressed="false" aria-label="View in English">EN</button>
      </div>
      <a class="btn btn-outline-light btn-sm" href="<?= url('auth/login') ?>" data-i18n="team">Equipo</a>
      <details class="publico-mobile-menu">
        <summary><span data-i18n="explore">Explorar</span><span class="publico-mobile-menu-icon" aria-hidden="true">+</span></summary>
        <div class="publico-mobile-links">
          <a href="#menu" data-i18n="navMenu">Menú</a>
          <a href="#para-llevar" data-i18n="navTakeaway">Para llevar</a>
          <a href="#nosotros" data-i18n="navAbout">Nosotros</a>
          <a href="#apparel" data-i18n="navApparel">Apparel</a>
          <a href="#ink-lab">Ink Lab</a>
          <a href="#dj-sessions">DJ Sessions</a>
          <a href="#morning-beats">Morning Beats</a>
          <a href="#visitanos" data-i18n="navVisit">Visítanos</a>
          <a href="<?= url('auth/login') ?>" data-i18n="teamAccess">Acceso equipo</a>
        </div>
      </details>
    </nav>
  </header>
  <?php require $archivo; ?>
  <footer class="publico-pie">
    <div class="publico-pie-marca">BLCK.CO · <span data-i18n="brandDescriptor">Restaurant &amp; Coffee</span></div>
    <div class="publico-pie-legal" aria-label="Políticas legales de marca" data-i18n-aria="legalPolicies">
      <span class="publico-pie-etiqueta" data-i18n="brandPolicies">POLÍTICAS DE MARCA</span>
      <span data-i18n="legalLinks">Privacidad · Términos y condiciones · Cookies</span>
      <span class="publico-pie-pendiente" data-i18n="documentsPending">Documentos en preparación</span>
    </div>
    <div class="publico-pie-credito">
      <a class="publico-pie-copyright" href="https://arqe-digital.vercel.app/" target="_blank" rel="noopener noreferrer" data-i18n="copyright">© 2026 ARQÉ DIGITAL by Joan Castillo · Construido en Colombia</a>
    </div>
  </footer>
  <script src="<?= asset('app.js') ?>?v=<?= filemtime(__DIR__ . '/app.js') ?>"></script>
</body>
</html>
