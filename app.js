// JS general de blck.co: confirmaciones con modal y validación visual de formularios.
document.addEventListener('DOMContentLoaded', () => {
  // 1) Confirmación antes de acciones destructivas: <form data-confirm="texto">
  const modalEl = document.getElementById('modalConfirmar');
  if (modalEl) {
    const modal = new bootstrap.Modal(modalEl);
    let formPendiente = null;
    document.querySelectorAll('form[data-confirm]').forEach((f) => {
      f.addEventListener('submit', (ev) => {
        ev.preventDefault();
        formPendiente = f;
        document.getElementById('modalTexto').textContent = f.dataset.confirm;
        modal.show();
      });
    });
    document.getElementById('modalOk').addEventListener('click', () => {
      if (formPendiente) { formPendiente.submit(); }
    });
  }

  // 2) Validación en el navegador (el servidor valida de nuevo): <form class="needs-validation">
  document.querySelectorAll('form.needs-validation').forEach((f) => {
    f.addEventListener('submit', (ev) => {
      if (!f.checkValidity()) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      f.classList.add('was-validated');
    });
  });

  const copy = {
    es: {
      mainNavigation: 'Navegación principal', navMenu: 'Menú', navTakeaway: 'Para llevar', navAbout: 'Nosotros', navApparel: 'Apparel', team: 'Equipo',
      explore: 'Explorar', teamAccess: 'Acceso equipo', brandDescriptor: 'Restaurant & Coffee',
      legalPolicies: 'Políticas legales de marca', brandPolicies: 'Políticas de marca',
      legalLinks: 'Privacidad · Términos y condiciones · Cookies', documentsPending: 'Documentos en preparación',
      copyright: '© 2026 ARQÉ DIGITAL by Joan Castillo · Construido en Colombia',
      heroTagline: 'FOOD · COFFEE · EXPERIENCE', exploreExperience: 'Explorar la experiencia',
      menuSelection: 'SELECCIÓN BLCK.CO', menuTitle: 'El menú',
      menuSummary: 'CAFÉ · PANADERÍA · POSTRES · BEBIDAS', menuSearchLabel: 'Buscar en el menú',
      menuSearchPlaceholder: 'Ej. cappuccino, croissant...', filterCategories: 'Filtrar por categoría',
      noProductsFound: 'No encontramos productos con esa búsqueda. Prueba otro nombre o categoría.',
      emptyMenu: 'Pronto encontrarás nuestra selección en el menú.', soldOut: 'Agotado',
      aboutKicker: 'BLCK.CO · FOOD · COFFEE · CULTURE', aboutTitle: 'Sobre nosotros',
      aboutDescription: 'Una marca que nace de la oscuridad, transforma la energía creativa y conecta diferentes expresiones de cultura urbana.',
      aboutConnection: 'Café, gastronomía, moda, arte y música conviven en un mismo universo: BLCK.CO.',
      aboutSignature: 'FOOD · COFFEE · EXPERIENCE', aboutPillars: 'Explora BLCK.CO',
      aboutMenu: 'MENÚ', aboutTakeaway: 'PARA LLEVAR', aboutApparel: 'APPAREL',
      takeawayKicker: 'BLCK.CO · PARA DISFRUTAR DONDE QUIERAS',
      takeawayTitle: 'Para llevar',
      takeawayDescription: 'Pregunta en nuestro local por las opciones disponibles para llevar y disfruta BLCK.CO donde prefieras.',
      takeawayMenuLink: 'Explorar el menú',
      morningDescription: 'Empieza el día de una manera diferente. Música electrónica seleccionada, café de especialidad, conexiones significativas y una comunidad que celebra la creatividad, la cultura y el bienestar.',
      curatedHouse: 'HOUSE SELECCIONADO', creativeCommunity: 'COMUNIDAD CREATIVA',
      apparelDescription: 'Una futura línea urbana de BLCK.CO, concebida como parte de su universo creativo.',
      upcomingLines: 'Líneas previstas', caps: 'Gorras', accessories: 'Accesorios',
      apparelPending: 'Colección en preparación · catálogo y disponibilidad aún no publicados.',
      brandExperiences: 'Experiencias BLCK.CO',
      inkDescription: 'Un espacio para el arte del tatuaje y la expresión personal.',
      exploreTattooStyles: 'Explorar estilos de tatuaje', minimalist: 'Minimalista', realism: 'Realismo',
      blackworkDescription: 'Contrastes marcados y composiciones en tinta negra. Explora el lenguaje visual del Blackwork.',
      inkPending: 'Explorador informativo; perfiles y citas, próximamente.',
      djDescription: 'Un punto de encuentro para la música y la cultura en BLCK.CO.',
      djActivate: 'Reproducir house original', djPause: 'Pausar música',
      djInactive: 'House sintetizado en tiempo real · sin pistas ni samples de terceros.',
      djActive: 'House en reproducción · visualizador sincronizado con el audio.',
      djError: 'No se pudo iniciar el audio. Revisa la configuración de sonido del navegador.',
      navVisit: 'Visítanos', visitKicker: 'BLCK.CO · SAMPER MENDOZA',
      visitTitle: 'Visítanos', visitAddress: 'Carrera 19 #23-14, Samper Mendoza',
      visitDays: 'Atención de martes a sábado.', openMap: 'Abrir ubicación en Google Maps',
      all: 'Todos', resultSingular: 'producto encontrado', resultPlural: 'productos encontrados',
      chooseSpanish: 'Ver la página en español', chooseEnglish: 'Ver la página en inglés',
    },
    en: {
      mainNavigation: 'Main navigation', navMenu: 'Menu', navTakeaway: 'Takeaway', navAbout: 'About us', navApparel: 'Apparel', team: 'Team',
      explore: 'Explore', teamAccess: 'Team login', brandDescriptor: 'Restaurant & Coffee',
      legalPolicies: 'Brand legal policies', brandPolicies: 'BRAND POLICIES',
      legalLinks: 'Privacy · Terms and conditions · Cookies', documentsPending: 'Documents in preparation',
      copyright: '© 2026 ARQÉ DIGITAL by Joan Castillo · Made in Colombia',
      heroTagline: 'FOOD · COFFEE · EXPERIENCE', exploreExperience: 'Explore the experience',
      menuSelection: 'THE BLCK.CO SELECTION', menuTitle: 'The Menu',
      menuSummary: 'COFFEE · BAKERY · DESSERTS · DRINKS', menuSearchLabel: 'Search the menu',
      menuSearchPlaceholder: 'E.g. cappuccino, croissant...', filterCategories: 'Filter by category',
      noProductsFound: 'No products match your search. Try another name or category.',
      emptyMenu: 'Our selection will be available soon.', soldOut: 'Sold out',
      aboutKicker: 'BLCK.CO · FOOD · COFFEE · CULTURE', aboutTitle: 'About us',
      aboutDescription: 'A brand born from darkness, transforming creative energy and connecting different expressions of urban culture.',
      aboutConnection: 'Coffee, food, fashion, art, and music come together in one universe: BLCK.CO.',
      aboutSignature: 'FOOD · COFFEE · EXPERIENCE', aboutPillars: 'Explore BLCK.CO',
      aboutMenu: 'MENU', aboutTakeaway: 'TAKEAWAY', aboutApparel: 'APPAREL',
      takeawayKicker: 'BLCK.CO · ENJOY IT WHEREVER YOU ARE',
      takeawayTitle: 'Takeaway',
      takeawayDescription: 'Ask at our location about available takeaway options and enjoy BLCK.CO wherever you like.',
      takeawayMenuLink: 'Explore the menu',
      morningDescription: 'Start your day differently. Curated electronic music, specialty coffee, meaningful connections, and a community built around creativity, culture, and well-being.',
      curatedHouse: 'CURATED HOUSE MUSIC', creativeCommunity: 'CREATIVE COMMUNITY',
      apparelDescription: 'An upcoming urban BLCK.CO collection, conceived as part of its creative universe.',
      upcomingLines: 'Upcoming collections', caps: 'Caps', accessories: 'Accessories',
      apparelPending: 'Collection in preparation · catalog and availability coming soon.',
      brandExperiences: 'BLCK.CO experiences',
      inkDescription: 'A space for tattoo art and personal expression.',
      exploreTattooStyles: 'Explore tattoo styles', minimalist: 'Minimalist', realism: 'Realism',
      blackworkDescription: 'Bold contrasts and compositions in black ink. Explore the visual language of Blackwork.',
      inkPending: 'Informational explorer; artist profiles and appointments coming soon.',
      djDescription: 'A meeting place for music and culture at BLCK.CO.',
      djActivate: 'Play original house', djPause: 'Pause music',
      djInactive: 'House synthesized in real time · no third-party tracks or samples.',
      djActive: 'House playing · visualizer synchronized with the audio.',
      djError: 'Audio could not start. Check your browser sound settings.',
      navVisit: 'Visit us', visitKicker: 'BLCK.CO · SAMPER MENDOZA',
      visitTitle: 'Visit us', visitAddress: 'Carrera 19 #23-14, Samper Mendoza',
      visitDays: 'Open Tuesday through Saturday.', openMap: 'Open location in Google Maps',
      all: 'All', resultSingular: 'product found', resultPlural: 'products found',
      chooseSpanish: 'View the page in Spanish', chooseEnglish: 'View the page in English',
    },
  };
  const categoriesEn = {
    'Café caliente': 'Hot Coffee',
    'Café frío': 'Iced Coffee',
    Bebidas: 'Drinks',
    Hamburguesas: 'Burgers',
    Panadería: 'Bakery',
    Cóctel: 'Cocktail',
    'Cerveza de la casa': 'House Beer',
    'Cócteles y cerveza de la casa': 'Cocktails & House Beer',
  };
  const sectionsEn = {
    'BLACK ICE COFFEE': 'BLACK ICED COFFEE',
    'BLACK COCTELES': 'BLACK COCKTAILS',
  };
  const descriptionsEn = {
    AMERICANO: 'Bold espresso softened with hot water. A clean, aromatic, well-balanced cup.',
    CAPPUCCINO: 'Full-bodied espresso with steamed milk and creamy foam. A smooth, aromatic, perfectly balanced combination.',
    ESPRESSO: 'A concentrated, bold coffee with pronounced body, golden crema, and aromatic notes that reveal the essence of great coffee. Small in size, big in character.',
    LATTE: 'Espresso with creamy milk.',
    'ESPRESSO MACCHIATO': 'Bold espresso topped with a delicate layer of milk foam. A balanced combination that preserves the coffee’s intensity with a smooth, creamy touch.',
    'MOCHA BLCK': 'The intensity of espresso meets dark chocolate and smooth steamed milk. A creamy, rich drink, perfect for chocolate lovers.',
    'COLD BREW': 'Coffee slowly cold-brewed for 18 hours. Smooth, refreshing, and aromatic, with natural notes of chocolate and nuts.',
    'MOCHA FRAPPÉ': 'The perfect blend of coffee, dark chocolate, and crushed ice. A cold, creamy drink with an irresistible texture, ideal for coffee and chocolate lovers.',
    'ICED VANILLA LATTE': 'Bold espresso blended with cold milk, ice, and delicate vanilla notes. Creamy, balanced, and refreshing—perfect any time of day.',
    'BLACK CARAMEL LATTE': 'A blend of espresso, cold milk, and caramel with a sweet, toasted touch. Smooth, creamy, and irresistible.',
    'BLACK LIMONADE': 'A handcrafted, intensely black lemonade made with fresh lemon juice, bright citrus notes, and a delicate balance of sweetness and acidity.',
    'CHOCOLATE CALIENTE': '60% cocoa, whole milk.',
    'BLACK CLASSIC BURGER': 'A classic with BLCK.CO character. Black bun, grilled beef, melted cheddar, fresh lettuce, tomato, and house sauce. Simple, juicy, and balanced.',
    'BUTTER CROISSANT': 'An artisan butter croissant made with delicate laminated dough, a golden, crisp exterior, and a soft, airy center. A French bakery classic, perfect with our coffee.',
    'BLACK CHOCOLATE CAKE': 'A moist, fluffy chocolate cake with an intense cocoa flavor and a smooth chocolate ganache topping. A classic to enjoy with coffee.',
    'BLACK ESPRESSO MARTINI': 'A bold cocktail combining espresso, vodka, and coffee liqueur. Served chilled, with a smooth texture and a foamy top finished with coffee beans.',
    'BLACK MARGARITA': 'A bold margarita with a sophisticated presentation. Its deep black color contrasts with citrusy lime and fresh tequila, balancing citrus, sweetness, and a hint of tartness. Served in a traditional margarita glass with a black rim.',
    'BLCK.CO COFFEE BEER': 'An artisan beer infused with coffee, with an intense character and toasted aroma. It combines notes of freshly brewed coffee, dark chocolate, and roasted malt, with a balanced body and smooth finish. A unique experience for coffee and beer lovers.',
  };
  const presentationsEn = {
    'BLCK.CO COFFEE BEER': 'Serving: 330 ml bottle.',
  };
  const productNamesEn = {
    'AGUA CON GAS': 'Sparkling Water',
    'CHOCOLATE CALIENTE': 'Hot Chocolate',
  };
  const imageDescriptionsEn = {
    AMERICANO: 'A white cup of bold Americano coffee on a saucer',
    CAPPUCCINO: 'Cappuccino with milk foam and latte art in a white cup',
    ESPRESSO: 'Espresso with golden crema in a black cup on a black saucer',
    'ESPRESSO MACCHIATO': 'Espresso macchiato in a black cup with milk foam',
    'MOCHA BLCK': 'Mocha with latte art in a cup beside pieces of dark chocolate',
    'COLD BREW': 'Dark cold brew with ice served in a glass',
    'MOCHA FRAPPÉ': 'Mocha frappé topped with whipped cream and chocolate sauce in a tall glass',
    'ICED VANILLA LATTE': 'Iced vanilla latte with espresso, cold milk, and ice in a clear glass',
    'BLACK CARAMEL LATTE': 'Black Caramel Latte with ice and caramel in a clear glass',
    'BLACK LIMONADE': 'Black Limonade with a lemon slice',
    'BLACK CLASSIC BURGER': 'Classic burger with a black bun, beef, cheddar, lettuce, and tomato',
    'BUTTER CROISSANT': 'Golden, flaky artisan butter croissant on a black plate',
    'BLACK CHOCOLATE CAKE': 'Slice of chocolate cake topped with ganache on a black plate',
    'BLACK ESPRESSO MARTINI': 'Black Espresso Martini in a glass topped with foam and coffee beans',
    'BLACK MARGARITA': 'Black Margarita in a traditional glass with lime and a black rim',
    'BLCK.CO COFFEE BEER': 'BLCK.CO Coffee Beer artisan beer bottle, 330 ml, beside coffee beans',
  };
  const inkDescriptions = {
    es: {
      blackwork: 'Contrastes marcados y composiciones en tinta negra. Explora el lenguaje visual del Blackwork.',
      'fine-line': 'Trazos delicados y precisos que construyen ilustraciones de apariencia ligera.',
      minimalista: 'Diseños esenciales que expresan una idea con formas simples y limpias.',
      realismo: 'Luz, sombra y detalle se combinan para crear imágenes de apariencia realista.',
    },
    en: {
      blackwork: 'Bold contrasts and compositions in black ink. Explore the visual language of Blackwork.',
      'fine-line': 'Delicate, precise lines come together to create light, refined illustrations.',
      minimalista: 'Essential designs express an idea through clean, simple shapes.',
      realismo: 'Light, shadow, and detail come together to create lifelike images.',
    },
  };
  const languageButtons = [...document.querySelectorAll('[data-language]')];
  let currentLanguage = languageButtons.length && localStorage.getItem('blckco-language') === 'en' ? 'en' : 'es';
  let djPlaybackState = 'inactive';
  let refreshMenuLanguage = () => {};

  function translatedCategory(category) {
    return currentLanguage === 'en' ? (categoriesEn[category] || category) : category;
  }

  function updateLanguage(language) {
    currentLanguage = language;
    if (languageButtons.length) localStorage.setItem('blckco-language', language);
    const strings = copy[language];
    document.documentElement.lang = language;
    languageButtons.forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.language === language));
      button.setAttribute('aria-label', strings[button.dataset.language === 'es' ? 'chooseSpanish' : 'chooseEnglish']);
    });
    if (document.body.classList.contains('public-site')) {
      document.title = language === 'en' ? 'Menu · blck.co' : 'El menú · blck.co';
    }
    document.querySelectorAll('[data-i18n]').forEach((element) => {
      const translation = strings[element.dataset.i18n];
      if (translation !== undefined) element.textContent = translation;
    });
    document.querySelectorAll('[data-i18n-aria]').forEach((element) => {
      const translation = strings[element.dataset.i18nAria];
      if (translation !== undefined) element.setAttribute('aria-label', translation);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
      const translation = strings[element.dataset.i18nPlaceholder];
      if (translation !== undefined) element.setAttribute('placeholder', translation);
    });
    document.querySelectorAll('.menu-grupo-titulo[data-section-key]').forEach((element) => {
      const key = element.dataset.sectionKey;
      element.textContent = language === 'en' ? (sectionsEn[key] || key) : (key === 'BLACK COCTELES' ? 'BLACK CÓCTELES' : key);
    });
    document.querySelectorAll('.producto-categoria[data-category-es]').forEach((element) => {
      element.textContent = translatedCategory(element.dataset.categoryEs);
    });
    document.querySelectorAll('.menu-productos[data-category-es]').forEach((element) => {
      element.setAttribute('aria-label', translatedCategory(element.dataset.categoryEs));
    });
    document.querySelectorAll('[data-product-name]').forEach((element) => {
      const name = element.dataset.productName.trim().toLocaleUpperCase();
      element.textContent = language === 'en' ? (productNamesEn[name] || element.dataset.productName) : element.dataset.productName;
    });
    document.querySelectorAll('[data-product-alt-es]').forEach((element) => {
      const name = element.closest('.producto-card')?.querySelector('[data-product-name]')?.dataset.productName.trim().toLocaleUpperCase();
      if (language === 'en' && name && imageDescriptionsEn[name]) element.alt = imageDescriptionsEn[name];
      else if (language === 'es') element.alt = element.dataset.productAltEs;
    });
    document.querySelectorAll('[data-product-description]').forEach((element) => {
      const name = element.dataset.productDescription.trim().toLocaleUpperCase();
      if (language === 'en' && descriptionsEn[name]) element.textContent = descriptionsEn[name];
      else if (language === 'es') element.textContent = element.dataset.descriptionEs;
    });
    document.querySelectorAll('[data-product-presentation]').forEach((element) => {
      const name = element.dataset.productPresentation.trim().toLocaleUpperCase();
      if (language === 'en' && presentationsEn[name]) element.textContent = presentationsEn[name];
      else if (language === 'es') element.textContent = element.dataset.presentationEs;
    });
    const selectedInkStyle = document.querySelector('[data-ink-style][aria-pressed="true"]');
    const inkDescription = document.querySelector('[data-ink-description]');
    if (selectedInkStyle && inkDescription) {
      inkDescription.textContent = inkDescriptions[language][selectedInkStyle.dataset.inkStyle];
    }
    updateFilterLanguage();
    updateDjLanguage();
  }

  const menuFilters = document.querySelector('[data-menu-filters]');
  const menuSearch = document.querySelector('[data-menu-search]');
  const menuCategories = document.querySelector('[data-menu-categories]');
  const menuResults = document.querySelector('[data-menu-results]');
  const menuEmpty = document.querySelector('[data-menu-empty]');
  const menuCards = [...document.querySelectorAll('#menu .producto-card')];
  if (menuFilters && menuSearch && menuCategories && menuResults && menuEmpty && menuCards.length) {
    const categories = [...new Set(menuCards.map((card) =>
      card.querySelector('.producto-categoria')?.dataset.categoryEs
    ).filter(Boolean))];
    let activeCategory = '';

    const allButton = document.createElement('button');
    allButton.type = 'button';
    allButton.className = 'menu-filtro';
    allButton.dataset.filterAll = 'true';
    allButton.setAttribute('aria-pressed', 'true');
    menuCategories.append(allButton);

    categories.forEach((category) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'menu-filtro';
      button.dataset.filterCategory = category;
      button.setAttribute('aria-pressed', 'false');
      button.addEventListener('click', () => {
        activeCategory = category;
        allButton.setAttribute('aria-pressed', 'false');
        menuCategories.querySelectorAll('.menu-filtro').forEach((option) => {
          option.setAttribute('aria-pressed', String(option === button));
        });
        filterMenu();
      });
      menuCategories.append(button);
    });

    function filterMenu() {
      const query = menuSearch.value.trim().toLocaleLowerCase();
      let visibleCount = 0;
      document.querySelectorAll('#menu .menu-grupo').forEach((group) => {
        let groupCount = 0;
        group.querySelectorAll('.producto-card').forEach((card) => {
          const category = card.querySelector('.producto-categoria')?.dataset.categoryEs || '';
          const matches = (!activeCategory || category === activeCategory)
            && (!query || card.textContent.toLocaleLowerCase().includes(query));
          card.hidden = !matches;
          groupCount += Number(matches);
        });
        group.hidden = groupCount === 0;
        visibleCount += groupCount;
      });
      const strings = copy[currentLanguage];
      menuResults.textContent = `${visibleCount} ${visibleCount === 1 ? strings.resultSingular : strings.resultPlural}`;
      menuEmpty.hidden = visibleCount > 0;
    }

    allButton.addEventListener('click', () => {
      activeCategory = '';
      menuCategories.querySelectorAll('.menu-filtro').forEach((option) => {
        option.setAttribute('aria-pressed', String(option === allButton));
      });
      filterMenu();
    });
    menuSearch.addEventListener('input', filterMenu);
    menuFilters.hidden = false;
    refreshMenuLanguage = () => {
      allButton.textContent = copy[currentLanguage].all;
      menuCategories.querySelectorAll('[data-filter-category]').forEach((button) => {
        button.textContent = translatedCategory(button.dataset.filterCategory);
      });
      filterMenu();
    };
    filterMenu();
  }

  function updateFilterLanguage() {
    refreshMenuLanguage();
  }

  function updateDjLanguage() {
    const djToggle = document.querySelector('[data-dj-toggle]');
    const djStatus = document.querySelector('[data-dj-status]');
    if (!djToggle || !djStatus) return;
    const active = djPlaybackState === 'active';
    djToggle.textContent = copy[currentLanguage][active ? 'djPause' : 'djActivate'];
    djStatus.textContent = copy[currentLanguage][djPlaybackState === 'error' ? 'djError' : active ? 'djActive' : 'djInactive'];
    djToggle.setAttribute('aria-pressed', String(active));
  }

  languageButtons.forEach((button) => {
    button.addEventListener('click', () => updateLanguage(button.dataset.language));
  });
  updateLanguage(currentLanguage);

  document.querySelectorAll('.publico-mobile-links a[href^="#"]').forEach((link) => {
    link.addEventListener('click', () => {
      link.closest('.publico-mobile-menu')?.removeAttribute('open');
    });
  });
  document.querySelectorAll('.publico-mobile-menu').forEach((menu) => {
    const icon = menu.querySelector('.publico-mobile-menu-icon');
    menu.addEventListener('toggle', () => {
      if (icon) icon.textContent = menu.open ? '-' : '+';
    });
  });

  const inkDescription = document.querySelector('[data-ink-description]');
  if (inkDescription) {
    document.querySelectorAll('[data-ink-style]').forEach((button) => {
      button.addEventListener('click', () => {
        document.querySelectorAll('[data-ink-style]').forEach((opcion) => {
          opcion.setAttribute('aria-pressed', String(opcion === button));
        });
        inkDescription.textContent = inkDescriptions[currentLanguage][button.dataset.inkStyle];
      });
    });
  }

  const djToggle = document.querySelector('[data-dj-toggle]');
  const djVisualizer = document.querySelector('[data-dj-visualizer]');
  const djStatus = document.querySelector('[data-dj-status]');
  if (djToggle && djVisualizer && djStatus) {
    const djBars = [...djVisualizer.querySelectorAll('.dj-barra')];
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    let audioContext = null;
    let masterGain = null;
    let analyser = null;
    let noiseBuffer = null;
    let scheduler = null;
    let visualFrame = null;
    let suspendTimeout = null;
    let nextStepTime = 0;
    let step = 0;
    const bpm = 122;
    const stepDuration = 60 / bpm / 4;
    const bassRoots = [110, 87.31, 130.81, 98];
    const bassPattern = [0, 3, 6, 8, 11, 14];
    const bassDegree = { 0: 1, 3: 1.5, 6: 1.26, 8: 1, 11: 1.5, 14: 1.26 };
    const chords = [
      [220, 261.63, 329.63, 392, 493.88],
      [174.61, 220, 261.63, 329.63, 392],
      [261.63, 329.63, 392, 493.88, 587.33],
      [196, 246.94, 293.66, 329.63, 392],
    ];

    function createAudioGraph() {
      if (!AudioContext) {
        throw new Error('Web Audio API is not supported by this browser.');
      }

      audioContext = new AudioContext();
      masterGain = audioContext.createGain();
      analyser = audioContext.createAnalyser();
      analyser.fftSize = 128;
      analyser.smoothingTimeConstant = .72;
      masterGain.gain.value = 0;
      masterGain.connect(analyser);
      analyser.connect(audioContext.destination);

      const padBus = audioContext.createGain();
      const delay = audioContext.createDelay(.8);
      const delayFilter = audioContext.createBiquadFilter();
      const feedback = audioContext.createGain();
      padBus.gain.value = .62;
      delay.delayTime.value = .29;
      delayFilter.type = 'lowpass';
      delayFilter.frequency.value = 1900;
      feedback.gain.value = .16;
      padBus.connect(masterGain);
      padBus.connect(delay);
      delay.connect(delayFilter);
      delayFilter.connect(feedback);
      feedback.connect(delay);
      delay.connect(masterGain);

      const noise = audioContext.createBuffer(1, audioContext.sampleRate, audioContext.sampleRate);
      const samples = noise.getChannelData(0);
      for (let i = 0; i < samples.length; i++) {
        samples[i] = Math.random() * 2 - 1;
      }
      noiseBuffer = noise;
      return padBus;
    }

    function scheduleKick(time) {
      const oscillator = audioContext.createOscillator();
      const envelope = audioContext.createGain();
      oscillator.type = 'sine';
      oscillator.frequency.setValueAtTime(145, time);
      oscillator.frequency.exponentialRampToValueAtTime(48, time + .16);
      envelope.gain.setValueAtTime(.0001, time);
      envelope.gain.exponentialRampToValueAtTime(.72, time + .008);
      envelope.gain.exponentialRampToValueAtTime(.0001, time + .24);
      oscillator.connect(envelope);
      envelope.connect(masterGain);
      oscillator.start(time);
      oscillator.stop(time + .25);
    }

    function scheduleNoise(time, duration, volume, frequency, type, bursts = false) {
      const source = audioContext.createBufferSource();
      const filter = audioContext.createBiquadFilter();
      const envelope = audioContext.createGain();
      source.buffer = noiseBuffer;
      filter.type = type;
      filter.frequency.value = frequency;
      source.connect(filter);
      filter.connect(envelope);
      envelope.connect(masterGain);
      envelope.gain.setValueAtTime(.0001, time);

      if (bursts) {
        [[.005, volume], [.028, .0001], [.034, volume * .68], [.058, .0001], [.065, volume * .42], [duration, .0001]]
          .forEach(([offset, value]) => envelope.gain.exponentialRampToValueAtTime(value, time + offset));
      } else {
        envelope.gain.exponentialRampToValueAtTime(volume, time + .004);
        envelope.gain.exponentialRampToValueAtTime(.0001, time + duration);
      }

      source.start(time);
      source.stop(time + duration + .01);
    }

    function scheduleBass(time, frequency) {
      const oscillator = audioContext.createOscillator();
      const filter = audioContext.createBiquadFilter();
      const envelope = audioContext.createGain();
      oscillator.type = 'triangle';
      oscillator.frequency.setValueAtTime(frequency, time);
      filter.type = 'lowpass';
      filter.frequency.setValueAtTime(430, time);
      filter.frequency.exponentialRampToValueAtTime(190, time + .2);
      envelope.gain.setValueAtTime(.0001, time);
      envelope.gain.exponentialRampToValueAtTime(.19, time + .012);
      envelope.gain.exponentialRampToValueAtTime(.0001, time + .24);
      oscillator.connect(filter);
      filter.connect(envelope);
      envelope.connect(masterGain);
      oscillator.start(time);
      oscillator.stop(time + .25);
    }

    function scheduleChord(time, notes, padBus) {
      notes.forEach((frequency) => {
        const oscillator = audioContext.createOscillator();
        const filter = audioContext.createBiquadFilter();
        const envelope = audioContext.createGain();
        oscillator.type = 'sawtooth';
        oscillator.frequency.value = frequency;
        oscillator.detune.value = Math.random() * 6 - 3;
        filter.type = 'lowpass';
        filter.frequency.value = 1450;
        envelope.gain.setValueAtTime(.0001, time);
        envelope.gain.exponentialRampToValueAtTime(.045, time + .38);
        envelope.gain.setValueAtTime(.045, time + 1.3);
        envelope.gain.exponentialRampToValueAtTime(.0001, time + 1.9);
        oscillator.connect(filter);
        filter.connect(envelope);
        envelope.connect(padBus);
        oscillator.start(time);
        oscillator.stop(time + 1.92);
      });
    }

    function scheduleStep(time, padBus) {
      const position = step % 16;
      const bar = Math.floor(step / 16) % chords.length;

      if (position === 0) scheduleChord(time, chords[bar], padBus);
      if (position % 4 === 0) scheduleKick(time);
      if (position === 4 || position === 12) scheduleNoise(time, .16, .2, 1700, 'bandpass', true);
      if (position % 2 === 0) {
        if (position === 6 || position === 14) {
          scheduleNoise(time, .24, .12, 7200, 'highpass');
        } else {
          scheduleNoise(time, .055, .065, 8200, 'highpass');
        }
      }
      if (bassPattern.includes(position)) {
        scheduleBass(time, bassRoots[bar] * bassDegree[position]);
      }

      step = (step + 1) % 64;
    }

    function scheduleAhead(padBus) {
      while (nextStepTime < audioContext.currentTime + .12) {
        scheduleStep(nextStepTime, padBus);
        nextStepTime += stepDuration;
      }
    }

    function animateVisualizer() {
      const levels = new Uint8Array(analyser.frequencyBinCount);
      analyser.getByteFrequencyData(levels);
      djBars.forEach((bar, index) => {
        const bin = Math.min(levels.length - 1, 1 + Math.floor(index * (levels.length - 2) / djBars.length));
        const energy = levels[bin] / 255;
        bar.style.transform = `scaleY(${.2 + energy * 4.6})`;
        bar.style.opacity = String(.42 + energy * .58);
      });
      visualFrame = window.requestAnimationFrame(animateVisualizer);
    }

    function resetVisualizer() {
      djBars.forEach((bar) => {
        bar.style.removeProperty('transform');
        bar.style.removeProperty('opacity');
      });
    }

    async     function startMusic() {
      if (!audioContext) {
        padBus = createAudioGraph();
      }

      if (suspendTimeout) {
        window.clearTimeout(suspendTimeout);
        suspendTimeout = null;
      }
      await audioContext.resume();
      const now = audioContext.currentTime;
      masterGain.gain.cancelScheduledValues(now);
      masterGain.gain.setValueAtTime(.0001, now);
      masterGain.gain.exponentialRampToValueAtTime(.34, now + .3);
      nextStepTime = now + .05;
      scheduler = window.setInterval(() => scheduleAhead(padBus), 25);
      animateVisualizer();
    }

    async function pauseMusic() {
      if (scheduler) {
        window.clearInterval(scheduler);
        scheduler = null;
      }
      if (visualFrame) {
        window.cancelAnimationFrame(visualFrame);
        visualFrame = null;
      }
      resetVisualizer();
      const now = audioContext.currentTime;
      masterGain.gain.cancelScheduledValues(now);
      masterGain.gain.setTargetAtTime(.0001, now, .015);
      suspendTimeout = window.setTimeout(() => {
        audioContext.suspend().catch((error) => console.error('Unable to suspend BLCK.CO audio:', error));
        suspendTimeout = null;
      }, 90);
    }

    let padBus = null;
    let djStarting = false;
    djToggle.addEventListener('click', async () => {
      if (djStarting) return;
      if (djPlaybackState === 'active') {
        await pauseMusic();
        djPlaybackState = 'inactive';
        djVisualizer.classList.remove('is-active');
        updateDjLanguage();
        return;
      }

      djStarting = true;
      djToggle.disabled = true;
      try {
        await startMusic();
        djPlaybackState = 'active';
        djVisualizer.classList.add('is-active');
        updateDjLanguage();
      } catch (error) {
        console.error('Unable to start BLCK.CO original house audio:', error);
        djPlaybackState = 'error';
        djVisualizer.classList.remove('is-active');
        updateDjLanguage();
      } finally {
        djStarting = false;
        djToggle.disabled = false;
      }
    });
  }

  const revealElements = document.querySelectorAll('.morning-beats [data-scroll-reveal]');
  if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    revealElements.forEach((element) => {
      element.classList.add('will-reveal');
      revealObserver.observe(element);
    });
  } else {
    revealElements.forEach((element) => element.classList.add('is-visible'));
  }
});
