<?php
$secciones = [];
$fotografias = [
    'BLACK COFFEE' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=82',
    'BLACK DRINKS' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=82',
    'BLACK FOOD' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=900&q=82',
    'BLACK DESSERTS' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=82',
];

foreach (($menu ?? []) as $categoria => $productos) {
    $normalizada = mb_strtolower($categoria, 'UTF-8');
    $contieneColdBrew = false;
    foreach ($productos as $productoMenu) {
        if (mb_strtolower($productoMenu['nombre'], 'UTF-8') === 'cold brew') {
            $contieneColdBrew = true;
            break;
        }
    }
    if ($contieneColdBrew || preg_match('/fr[ií]o/u', $normalizada)) {
        $seccion = 'BLACK ICE COFFEE';
    } elseif (preg_match('/caliente/u', $normalizada)) {
        $seccion = 'BLACK COFFEE';
    } elseif (preg_match('/caf[eé]|coffee/u', $normalizada)) {
        $seccion = 'BLACK COFFEE';
    } elseif (preg_match('/bebida|drink/u', $normalizada)) {
        $seccion = 'BLACK DRINKS';
    } elseif (preg_match('/postre|dessert/u', $normalizada)) {
        $seccion = 'BLACK DESSERTS';
    } elseif (preg_match('/panader|bakery|food/u', $normalizada)) {
        $seccion = 'BLACK FOOD';
    } else {
        $seccion = mb_strtoupper($categoria, 'UTF-8');
    }
    $secciones[$seccion][$categoria] = $productos;
}

$limonada = [
    'nombre' => 'Black Limonade',
    'descripcion' => 'Limonada artesanal de intenso color negro, elaborada con zumo de limón fresco, notas cítricas y un delicado equilibrio entre dulzor y acidez.',
    'precio' => 10000,
    'imagen' => 'black-limonade.png',
    'alt' => 'Black Limonade negra con una rodaja de limón',
];
$espressoMartini = [
    'nombre' => 'BLACK ESPRESSO MARTINI',
    'categoria' => 'Cóctel',
    'descripcion' => 'Un cóctel intenso que combina espresso, vodka y licor de café. Servido bien frío, con una textura suave y una capa de espuma coronada con granos de café.',
    'precio' => 28000,
    'imagen' => 'black-espresso-martini.png',
    'alt' => 'Black Espresso Martini en copa con espuma y granos de café',
];
$blackMargarita = [
    'nombre' => 'BLACK MARGARITA',
    'categoria' => 'Cóctel',
    'descripcion' => 'Una margarita de carácter intenso y presentación sofisticada. Su color negro profundo contrasta con el toque cítrico del limón y la frescura del tequila, creando una mezcla equilibrada entre notas cítricas, dulces y ligeramente ácidas. Servida en copa tradicional de margarita con escarchado negro.',
    'precio' => 30000,
    'imagen' => 'black-margarita.png',
    'alt' => 'Black Margarita en copa tradicional con limón y escarchado negro',
];
$bebidas = &$secciones['BLACK DRINKS']['Bebidas'];
$indiceAguaConGas = null;
foreach ($bebidas ?? [] as $indice => $bebida) {
    if (mb_strtolower($bebida['nombre'], 'UTF-8') === 'agua con gas') {
        $indiceAguaConGas = $indice + 1;
        break;
    }
}
if ($indiceAguaConGas === null) {
    $bebidas[] = $limonada;
} else {
    array_splice($bebidas, $indiceAguaConGas, 0, [$limonada]);
}
$indiceLimonada = null;
foreach ($bebidas as $indice => $bebida) {
    if ($bebida['nombre'] === $limonada['nombre']) {
        $indiceLimonada = $indice + 1;
        break;
    }
}
unset($bebidas);

$coffeeBeer = [
    'nombre' => 'BLCK.CO COFFEE BEER',
    'categoria' => 'Cerveza de la casa',
    'descripcion' => 'Cerveza artesanal infusionada con café, de carácter intenso y aroma tostado. Combina notas de café recién preparado, chocolate oscuro y malta tostada, con un cuerpo equilibrado y un final suave. Una experiencia diferente para los amantes del café y la cerveza.',
    'presentacion' => 'Presentación: botella de 330 ml.',
    'precio' => 12000,
    'imagen' => 'blckco-coffee-beer.png',
    'alt' => 'Botella de cerveza artesanal BLCK.CO Coffee Beer de 330 ml junto a granos de café',
];
$bebidasExistentes = &$secciones['BLACK DRINKS']['Bebidas'];
foreach ($bebidasExistentes ?? [] as $indice => $bebidaExistente) {
    if (str_contains(mb_strtolower($bebidaExistente['nombre'], 'UTF-8'), 'coffee beer')) {
        unset($bebidasExistentes[$indice]);
    }
}
$bebidasExistentes = array_values($bebidasExistentes ?? []);
unset($bebidasExistentes);
$secciones['BLCK.CO COFFEE BEER']['Cerveza de la casa'] = [$coffeeBeer];

foreach ($secciones as $tituloSeccion => &$categorias) {
    foreach ($categorias as $categoria => &$productos) {
        foreach ($productos as &$producto) {
            if (
                $tituloSeccion === 'BLACK COFFEE'
                && preg_match('/caliente/i', $categoria)
                && mb_strtolower($producto['nombre'], 'UTF-8') === 'americano'
            ) {
                $producto['descripcion'] = 'Espresso intenso, suavizado con agua caliente. Una taza limpia, aromática y equilibrada.';
                $producto['precio'] = 5000;
                $producto['imagen'] = 'americano.png';
                $producto['alt'] = 'Taza blanca de café americano intenso sobre un plato';
            }
            if (
                $tituloSeccion === 'BLACK COFFEE'
                && preg_match('/caliente/i', $categoria)
                && mb_strtolower($producto['nombre'], 'UTF-8') === 'cappuccino'
            ) {
                $producto['descripcion'] = 'Espresso de cuerpo intenso con leche vaporizada y espuma cremosa. Una combinación suave, aromática y perfectamente equilibrada.';
                $producto['precio'] = 7000;
                $producto['imagen'] = 'cappuccino.jpg';
                $producto['alt'] = 'Cappuccino con espuma de leche y arte latte en una taza blanca';
            }
            if (
                $tituloSeccion === 'BLACK COFFEE'
                && preg_match('/caliente/i', $categoria)
                && mb_strtolower($producto['nombre'], 'UTF-8') === 'espresso'
            ) {
                $producto['descripcion'] = 'Café concentrado de extracción intensa, con cuerpo pronunciado, crema dorada y notas aromáticas que revelan la esencia del buen café. Pequeño en tamaño, intenso en personalidad.';
                $producto['precio'] = 5500;
                $producto['imagen'] = 'espresso.png';
                $producto['alt'] = 'Espresso con crema dorada en una taza negra sobre plato negro';
            }
            if (
                $tituloSeccion === 'BLACK COFFEE'
                && preg_match('/caliente/i', $categoria)
                && mb_strtolower($producto['nombre'], 'UTF-8') === 'espresso macchiato'
            ) {
                $producto['descripcion'] = 'Espresso intenso coronado con una delicada capa de espuma de leche. Una combinación equilibrada que conserva la fuerza del café con un toque suave y cremoso.';
                $producto['precio'] = 6500;
                $producto['imagen'] = 'espresso-macchiato.png';
                $producto['alt'] = 'Espresso macchiato en taza negra con espuma de leche';
            }
            if (
                $tituloSeccion === 'BLACK COFFEE'
                && preg_match('/caliente/i', $categoria)
                && mb_strtolower($producto['nombre'], 'UTF-8') === 'mocha blck'
            ) {
                $producto['descripcion'] = 'La intensidad del espresso se encuentra con el chocolate oscuro y la suavidad de la leche vaporizada. Una bebida cremosa, profunda y perfecta para los amantes del chocolate.';
                $producto['precio'] = 10000;
                $producto['imagen'] = 'mocha-blck.png';
                $producto['alt'] = 'Mocha con arte latte en una taza junto a trozos de chocolate oscuro';
            }
            if (
                $tituloSeccion === 'BLACK ICE COFFEE'
                && mb_strtolower($producto['nombre'], 'UTF-8') === 'cold brew'
            ) {
                $producto['descripcion'] = 'Café de extracción lenta en frío durante 18 horas. Una bebida suave, refrescante y aromática, con notas naturales de chocolate y frutos secos.';
                $producto['precio'] = 10500;
                $producto['imagen'] = 'cold-brew.png';
                $producto['alt'] = 'Cold brew oscuro con hielo servido en vaso de cristal';
            }
            if (
                $tituloSeccion === 'BLACK ICE COFFEE'
                && str_contains(mb_strtolower($producto['nombre'], 'UTF-8'), 'moca')
            ) {
                $producto['nombre'] = 'MOCHA FRAPPÉ';
                $producto['descripcion'] = 'La combinación perfecta de café, chocolate oscuro y hielo triturado. Una bebida fría, cremosa y de textura irresistible, ideal para los amantes del café y el chocolate.';
                $producto['precio'] = 12500;
                $producto['imagen'] = 'mocha-frappe.png';
                $producto['alt'] = 'Mocha frappé con crema batida y salsa de chocolate en vaso alto';
            }
        }
        unset($producto);
    }
    unset($productos);
}
unset($categorias);

$icedVanillaLatte = [
    'nombre' => 'ICED VANILLA LATTE',
    'descripcion' => 'Espresso intenso combinado con leche fría, hielo y delicadas notas de vainilla. Cremoso, equilibrado y refrescante, perfecto para cualquier momento del día.',
    'precio' => 11000,
    'imagen' => 'iced-vanilla-latte.png',
    'alt' => 'Iced vanilla latte con espresso, leche fría y hielo en vaso transparente',
];
$blackCaramelLatte = [
    'nombre' => 'BLACK CARAMEL LATTE',
    'descripcion' => 'Una mezcla de espresso, leche fría y caramelo, con un toque dulce y tostado. Suave, cremoso y con una presentación irresistible.',
    'precio' => 12000,
    'imagen' => 'black-caramel-latte.png',
    'alt' => 'Black Caramel Latte con hielo y caramelo en vaso transparente',
];
$secciones['BLACK ICE COFFEE']['Café frío'] ??= [];
$cafeFrio = &$secciones['BLACK ICE COFFEE']['Café frío'];
foreach (array_reverse(array_keys($cafeFrio ?? [])) as $indice) {
    if (mb_strtolower($cafeFrio[$indice]['nombre'], 'UTF-8') === 'iced vanilla latte') {
        array_splice($cafeFrio, $indice, 1);
    }
}
$indiceUltimoLatteFrio = null;
foreach ($cafeFrio as $indice => $productoFrio) {
    if (in_array(mb_strtolower($productoFrio['nombre'], 'UTF-8'), ['mocha frappé', 'iced vanilla latte'], true)) {
        $indiceUltimoLatteFrio = $indice;
    }
}
if ($indiceUltimoLatteFrio === null) {
    $cafeFrio[] = $icedVanillaLatte;
    $indiceUltimoLatteFrio = array_key_last($cafeFrio);
} else {
    array_splice($cafeFrio, $indiceUltimoLatteFrio + 1, 0, [$icedVanillaLatte]);
    $indiceUltimoLatteFrio++;
}
foreach (array_reverse(array_keys($cafeFrio)) as $indice) {
    if (mb_strtolower($cafeFrio[$indice]['nombre'], 'UTF-8') === 'black caramel latte') {
        array_splice($cafeFrio, $indice, 1);
        if ($indice < $indiceUltimoLatteFrio) {
            $indiceUltimoLatteFrio--;
        }
    }
}
array_splice($cafeFrio, $indiceUltimoLatteFrio + 1, 0, [$blackCaramelLatte]);
unset($cafeFrio);

$macchiato = [
    'nombre' => 'Espresso Macchiato',
    'descripcion' => 'Espresso intenso coronado con una delicada capa de espuma de leche. Una combinación equilibrada que conserva la fuerza del café con un toque suave y cremoso.',
    'precio' => 6500,
    'imagen' => 'espresso-macchiato.png',
    'alt' => 'Espresso macchiato en taza negra con espuma de leche',
];
$mochaBlck = [
    'nombre' => 'MOCHA BLCK',
    'descripcion' => 'La intensidad del espresso se encuentra con el chocolate oscuro y la suavidad de la leche vaporizada. Una bebida cremosa, profunda y perfecta para los amantes del chocolate.',
    'precio' => 10000,
    'imagen' => 'mocha-blck.png',
    'alt' => 'Mocha con arte latte en una taza junto a trozos de chocolate oscuro',
];
$cafeCaliente = &$secciones['BLACK COFFEE']['Café caliente'];
$indiceMacchiato = null;
foreach ($cafeCaliente ?? [] as $indice => $producto) {
    if (mb_strtolower($producto['nombre'], 'UTF-8') === 'espresso macchiato') {
        $indiceMacchiato = $indice;
        break;
    }
}
if ($indiceMacchiato === null) {
    $cafeCaliente[] = $macchiato;
    $indiceMacchiato = array_key_last($cafeCaliente);
}

foreach (array_reverse(array_keys($cafeCaliente)) as $indice) {
    if (mb_strtolower($cafeCaliente[$indice]['nombre'], 'UTF-8') === 'mocha blck') {
        array_splice($cafeCaliente, $indice, 1);
        if ($indice < $indiceMacchiato) {
            $indiceMacchiato--;
        }
    }
}
array_splice($cafeCaliente, $indiceMacchiato + 1, 0, [$mochaBlck]);
unset($cafeCaliente);

$blackChocolateCake = [
    'nombre' => 'BLACK CHOCOLATE CAKE',
    'descripcion' => 'Torta de chocolate húmeda y esponjosa, con intenso sabor a cacao y una cubierta suave de ganache de chocolate. Un clásico para acompañar con café.',
    'precio' => 12000,
    'imagen' => 'black-chocolate-cake.png',
    'alt' => 'Porción de torta de chocolate con cubierta de ganache sobre un plato negro',
];
$postres = &$secciones['BLACK DESSERTS']['Postres'];
$postres = array_values(array_filter(
    $postres ?? [],
    static fn($postre) => mb_strtolower($postre['nombre'], 'UTF-8') !== 'black chocolate cake'
));
unset($postres);

$categoriasCafeOrdenadas = [];
foreach ($secciones['BLACK COFFEE'] ?? [] as $categoria => $productos) {
    $nombresProductos = array_map(
        static fn($producto) => mb_strtolower($producto['nombre'], 'UTF-8'),
        $productos
    );
    $esCafeCaliente = stripos($categoria, 'caliente') !== false
        || (bool)array_intersect($nombresProductos, ['americano', 'cappuccino', 'espresso', 'latte', 'espresso macchiato', 'mocha blck']);
    $esCafeFrio = stripos($categoria, 'fr') !== false
        || (bool)array_intersect($nombresProductos, ['cold brew', 'frappé de moca']);

    if ($esCafeCaliente) {
        $categoriasCafeOrdenadas['Café caliente'] = array_merge(
            $categoriasCafeOrdenadas['Café caliente'] ?? [],
            $productos
        );
    } elseif ($esCafeFrio) {
        $categoriasCafeOrdenadas['Café frío'] = array_merge(
            $categoriasCafeOrdenadas['Café frío'] ?? [],
            $productos
        );
    } else {
        $categoriasCafeOrdenadas[$categoria] = $productos;
    }
}
$secciones['BLACK COFFEE'] = $categoriasCafeOrdenadas;

$categoriasCafeFrioOrdenadas = [];
foreach ($secciones['BLACK ICE COFFEE'] ?? [] as $categoria => $productos) {
    $categoriasCafeFrioOrdenadas['Café frío'] = array_merge(
        $categoriasCafeFrioOrdenadas['Café frío'] ?? [],
        $productos
    );
}
if ($categoriasCafeFrioOrdenadas) {
    $secciones['BLACK ICE COFFEE'] = $categoriasCafeFrioOrdenadas;
}

$seccionesOrdenadas = [];
foreach (['BLACK COFFEE', 'BLACK ICE COFFEE', 'BLACK DRINKS', 'BLACK FOOD', 'BLACK COCTELES', 'BLCK.CO COFFEE BEER'] as $seccionPrioritaria) {
    if (isset($secciones[$seccionPrioritaria])) {
        $seccionesOrdenadas[$seccionPrioritaria] = $secciones[$seccionPrioritaria];
    }
}
foreach ($secciones as $tituloSeccion => $categorias) {
    if (!isset($seccionesOrdenadas[$tituloSeccion])) {
        $seccionesOrdenadas[$tituloSeccion] = $categorias;
    }
}
$secciones = $seccionesOrdenadas;

$categoriasPanaderia = [];
$panaderiaProductos = [];
foreach ($secciones['BLACK FOOD'] ?? [] as $categoria => $productos) {
    if (stripos($categoria, 'panader') !== false) {
        $panaderiaProductos = array_merge($panaderiaProductos, $productos);
    } else {
        $categoriasPanaderia[$categoria] = $productos;
    }
}
if ($panaderiaProductos) {
    $categoriasPanaderia['Panadería'] = $panaderiaProductos;
}
$secciones['BLACK FOOD'] = $categoriasPanaderia;

$croissant = [
    'nombre' => 'BUTTER CROISSANT',
    'descripcion' => 'Croissant artesanal de mantequilla, elaborado con masa delicadamente hojaldrada, exterior dorado y crujiente e interior suave y aireado. Una pieza clásica de panadería francesa, perfecta para acompañar nuestro café.',
    'precio' => 6000,
    'imagen' => 'butter-croissant.png',
    'alt' => 'Croissant artesanal de mantequilla, dorado y hojaldrado sobre un plato negro',
];
$panaderia = &$secciones['BLACK FOOD']['Panadería'];
$productosPanaderiaActualizados = [];
$croissantActualizado = false;
foreach ($panaderia ?? [] as $productoPanaderia) {
    if (mb_strtolower($productoPanaderia['nombre'], 'UTF-8') === 'croissant') {
        if ($croissantActualizado) {
            continue;
        }
        $productosPanaderiaActualizados[] = array_merge($productoPanaderia, $croissant);
        $croissantActualizado = true;
        continue;
    }
    if (mb_strtolower($productoPanaderia['nombre'], 'UTF-8') === 'butter croissant') {
        if ($croissantActualizado) {
            continue;
        }
        $productosPanaderiaActualizados[] = array_merge($productoPanaderia, $croissant);
        $croissantActualizado = true;
        continue;
    }
    $productosPanaderiaActualizados[] = $productoPanaderia;
}
if (!$croissantActualizado) {
    $productosPanaderiaActualizados[] = $croissant;
}
$panaderia = $productosPanaderiaActualizados;
$productosPanaderiaActualizados = [];
$tortaReemplazoAgregada = false;
foreach ($panaderia as $productoPanaderia) {
    $nombrePanaderia = mb_strtolower($productoPanaderia['nombre'], 'UTF-8');
    if (in_array($nombrePanaderia, ['pan de bono', 'black chocolate cake'], true)) {
        if (!$tortaReemplazoAgregada) {
            $productosPanaderiaActualizados[] = array_merge($productoPanaderia, $blackChocolateCake);
            $tortaReemplazoAgregada = true;
        }
        continue;
    }
    $productosPanaderiaActualizados[] = $productoPanaderia;
}
if (!$tortaReemplazoAgregada) {
    $productosPanaderiaActualizados[] = $blackChocolateCake;
}
$panaderia = $productosPanaderiaActualizados;
unset($panaderia);

$blackClassicBurger = [
    'nombre' => 'BLACK CLASSIC BURGER',
    'descripcion' => 'Una versión clásica con identidad BLCK.CO. Pan negro, carne de res a la plancha, queso cheddar fundido, lechuga fresca, tomate y salsa de la casa. Simple, jugosa y equilibrada.',
    'precio' => 20000,
    'imagen' => 'black-classic-burger-menu.png',
    'alt' => 'Hamburguesa clásica con pan negro, carne, queso cheddar, lechuga y tomate',
];
$hamburguesas = &$secciones['BLACK FOOD']['Hamburguesas'];
$indiceBurger = null;
foreach ($hamburguesas ?? [] as $indice => $productoBurger) {
    if (in_array(mb_strtolower($productoBurger['nombre'], 'UTF-8'), ['black burger', 'black classic burger'], true)) {
        $indiceBurger = $indice;
        break;
    }
}
if ($indiceBurger === null) {
    $hamburguesas[] = $blackClassicBurger;
} else {
    $hamburguesas[$indiceBurger] = array_merge($hamburguesas[$indiceBurger], $blackClassicBurger);
}
unset($hamburguesas);

$comidaOrdenada = [];
if (isset($secciones['BLACK FOOD']['Hamburguesas'])) {
    $comidaOrdenada['Hamburguesas'] = $secciones['BLACK FOOD']['Hamburguesas'];
}
foreach ($secciones['BLACK FOOD'] ?? [] as $categoria => $productos) {
    if ($categoria !== 'Hamburguesas') {
        $comidaOrdenada[$categoria] = $productos;
    }
}
$secciones['BLACK FOOD'] = $comidaOrdenada;
unset($secciones['BLACK DESSERTS']);
$secciones['BLACK COCTELES']['Cócteles y cerveza de la casa'] = [
    $espressoMartini,
    $blackMargarita,
    $coffeeBeer,
];
unset($secciones['BLCK.CO COFFEE BEER']);

$seccionesOrdenadas = [];
foreach (['BLACK COFFEE', 'BLACK ICE COFFEE', 'BLACK DRINKS', 'BLACK FOOD', 'BLACK COCTELES'] as $seccionPrioritaria) {
    if (isset($secciones[$seccionPrioritaria])) {
        $seccionesOrdenadas[$seccionPrioritaria] = $secciones[$seccionPrioritaria];
    }
}
foreach ($secciones as $tituloSeccion => $categorias) {
    if (!isset($seccionesOrdenadas[$tituloSeccion])) {
        $seccionesOrdenadas[$tituloSeccion] = $categorias;
    }
}
$secciones = $seccionesOrdenadas;
?>
<section class="publico-hero" aria-labelledby="heroTitulo">
  <div class="hero-contenido">
    <span class="hero-kicker">RESTAURANT &amp; COFFEE · EST. BLCK.CO</span>
    <h1 class="hero-titulo" id="heroTitulo">BLCK<span>BLACK ON BLACK</span></h1>
    <p class="hero-subtitulo" data-i18n="heroTagline">FOOD · COFFEE · EXPERIENCE</p>
    <a class="btn btn-acento" href="#menu" data-i18n="exploreExperience">Explore the experience</a>
  </div>
</section>

<section class="menu-lista" id="menu" aria-labelledby="menuTitulo">
  <div class="menu-cabecera">
    <div>
      <span class="seccion-kicker" data-i18n="menuSelection">SELECCIÓN BLCK.CO</span>
      <h2 id="menuTitulo" data-i18n="menuTitle">El menú</h2>
    </div>
    <p data-i18n="menuSummary">CAFÉ · PANADERÍA · POSTRES · BEBIDAS</p>
  </div>
  <div class="menu-filtros" data-menu-filters hidden>
    <label class="menu-busqueda">
      <span data-i18n="menuSearchLabel">Buscar en el menú</span>
      <input type="search" data-menu-search data-i18n-placeholder="menuSearchPlaceholder" placeholder="Ej. cappuccino, croissant..." autocomplete="off">
    </label>
    <div class="menu-categorias" data-menu-categories role="group" aria-label="Filtrar por categoría" data-i18n-aria="filterCategories"></div>
    <p class="menu-resultados" data-menu-results aria-live="polite"></p>
  </div>
  <p class="menu-sin-resultados" data-menu-empty data-i18n="noProductsFound" hidden>No encontramos productos con esa búsqueda. Prueba otro nombre o categoría.</p>

  <?php if (!$secciones): ?>
    <p class="menu-vacio" data-i18n="emptyMenu">Pronto encontrarás nuestra selección en el menú.</p>
  <?php endif; ?>

  <?php foreach ($secciones as $tituloSeccion => $categorias): ?>
    <section class="menu-grupo<?= in_array($tituloSeccion, ['BLACK COFFEE', 'BLACK ICE COFFEE'], true) ? ' menu-grupo-cafe' : '' ?>" aria-labelledby="grupo-<?= e(substr(hash('sha256', $tituloSeccion), 0, 10)) ?>">
      <h3 class="menu-grupo-titulo" data-section-key="<?= e($tituloSeccion) ?>" id="grupo-<?= e(substr(hash('sha256', $tituloSeccion), 0, 10)) ?>"><?= e($tituloSeccion === 'BLACK COCTELES' ? 'BLACK CÓCTELES' : $tituloSeccion) ?></h3>
      <?php foreach ($categorias as $categoria => $productos): ?>
        <?php
          if ($tituloSeccion === 'BLACK COCTELES') {
              $categoriaVista = 'Cócteles y cerveza de la casa';
          } elseif ($tituloSeccion === 'BLACK ICE COFFEE') {
              $categoriaVista = 'Café frío';
          } elseif ($tituloSeccion === 'BLACK FOOD' && preg_match('/panader|bakery/i', $categoria)) {
              $categoriaVista = 'Panadería';
          } elseif ($tituloSeccion === 'BLACK FOOD' && preg_match('/hamburg/i', $categoria)) {
              $categoriaVista = 'Hamburguesas';
          } elseif (preg_match('/caliente/i', $categoria)) {
              $categoriaVista = 'Café caliente';
          } elseif (preg_match('/fr[ií]o/i', $categoria) || array_filter($productos, static fn($p) => mb_strtolower($p['nombre'], 'UTF-8') === 'cold brew')) {
              $categoriaVista = 'Café frío';
          } else {
              $categoriaVista = $categoria;
          }
        ?>
        <div class="menu-productos" aria-label="<?= e($categoriaVista) ?>" data-category-es="<?= e($categoriaVista) ?>">
          <?php foreach ($productos as $p): ?>
            <?php $imagen = $p['imagen'] ?? $fotografias[$tituloSeccion] ?? $fotografias['BLACK FOOD']; ?>
            <?php
              $categoriaProductoVista = $p['categoria'] ?? $categoriaVista;
              if (in_array($tituloSeccion, ['BLACK COFFEE', 'BLACK ICE COFFEE'], true)) {
                  $categoriaProductoVista = $categoriaVista;
              } elseif ($tituloSeccion === 'BLACK FOOD') {
                  $categoriaProductoVista = $categoriaVista;
              }
            ?>
            <?php $esHamburguesaDestacada = mb_strtolower($p['nombre'], 'UTF-8') === 'black classic burger'; ?>
            <article class="producto-card<?= $esHamburguesaDestacada ? ' producto-card-destacado' : '' ?>">
              <div class="producto-imagen">
                <img src="<?= e($imagen) ?>" alt="<?= e($p['alt'] ?? '') ?>" <?= empty($p['alt']) ? 'aria-hidden="true"' : 'data-product-alt-es="' . e($p['alt']) . '"' ?> loading="lazy" decoding="async">
              </div>
              <div class="producto-info">
                <span class="producto-categoria" data-category-es="<?= e($categoriaProductoVista) ?>"><?= e($categoriaProductoVista) ?></span>
                <h4 class="producto-nombre" data-product-name="<?= e($p['nombre']) ?>"><?= e($p['nombre']) ?></h4>
                <?php if (!empty($p['descripcion'])): ?>
                  <p class="producto-descripcion" data-product-description="<?= e($p['nombre']) ?>" data-description-es="<?= e($p['descripcion']) ?>"><?= e($p['descripcion']) ?></p>
                <?php endif; ?>
                <?php if (!empty($p['presentacion'])): ?>
                  <p class="producto-presentacion" data-product-presentation="<?= e($p['nombre']) ?>" data-presentation-es="<?= e($p['presentacion']) ?>"><?= e($p['presentacion']) ?></p>
                <?php endif; ?>
                <div class="producto-pie">
                  <span class="producto-precio"><?= money($p['precio']) ?></span>
                  <?php if (isset($p['stock']) && (int)$p['stock'] <= 0): ?>
                    <span class="producto-agotado" data-i18n="soldOut">Agotado</span>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </section>

  <?php endforeach; ?>
</section>

<section class="para-llevar" id="para-llevar" aria-labelledby="paraLlevarTitulo">
  <div class="para-llevar-icono" aria-hidden="true">
    <svg viewBox="0 0 48 48"><path d="M12 17h24l3 25H9l3-25Z"/><path d="M17 19v-5a7 7 0 0 1 14 0v5M19 27c2 2 3 2 5 0s3-2 5 0"/></svg>
  </div>
  <div class="para-llevar-copy">
    <span class="seccion-kicker" data-i18n="takeawayKicker">BLCK.CO · PARA DISFRUTAR DONDE QUIERAS</span>
    <h2 id="paraLlevarTitulo" data-i18n="takeawayTitle">Para llevar</h2>
    <p data-i18n="takeawayDescription">Pregunta en nuestro local por las opciones disponibles para llevar y disfruta BLCK.CO donde prefieras.</p>
  </div>
  <a class="para-llevar-link" href="#menu" data-i18n="takeawayMenuLink">Explorar el menú <span aria-hidden="true">↗</span></a>
</section>

<section class="nosotros-section" id="nosotros" aria-labelledby="nosotrosTitulo">
  <div class="nosotros-contenido">
    <div class="nosotros-copy">
      <span class="seccion-kicker" data-i18n="aboutKicker">BLCK.CO · FOOD · COFFEE · CULTURE</span>
      <h2 id="nosotrosTitulo" data-i18n="aboutTitle">Sobre nosotros</h2>
      <p class="nosotros-manifiesto">RISE FROM THE BLACK.</p>
      <p class="nosotros-descripcion" data-i18n="aboutDescription">
        Una marca que nace de la oscuridad, transforma la energía creativa y conecta diferentes expresiones de cultura urbana.
      </p>
      <p class="nosotros-descripcion" data-i18n="aboutConnection">
        Café, gastronomía, moda, arte y música conviven en un mismo universo: BLCK.CO.
      </p>
    </div>
    <div class="nosotros-marca" aria-hidden="true">
      <img src="<?= asset('blckco-header-logo.png') ?>" alt="">
      <span data-i18n="aboutSignature">FOOD · COFFEE · EXPERIENCE</span>
    </div>
  </div>
  <nav class="nosotros-pilares" aria-label="Explora BLCK.CO" data-i18n-aria="aboutPillars">
    <a href="#menu">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11h15v3a6 6 0 0 1-6 6H9a6 6 0 0 1-6-6zM18 12h1a3 3 0 0 1 0 6h-2M7 3v3M12 2v4M17 4v2"/></svg>
      <span data-i18n="aboutMenu">MENÚ</span>
    </a>
    <a href="#para-llevar">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h12l1.5 13h-15L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3M9 14c1.5 1.5 2.5 1.5 4 0s2.5-1.5 4 0"/></svg>
      <span data-i18n="aboutTakeaway">PARA LLEVAR</span>
    </a>
    <a href="#apparel">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 7 5-1.5 2L12 7l-5.5 3L5 8zM4 13h16l-8 8z"/></svg>
      <span data-i18n="aboutApparel">APPAREL</span>
    </a>
    <a href="#ink-lab">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 9-9M14 4l6 6M12 6l6 6M3 21l4-1 13-13-3-3L4 17zM17 3l2-2 4 4-2 2"/></svg>
      <span>INK LAB</span>
    </a>
    <a href="#dj-sessions">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 13v-2a9 9 0 0 1 18 0v2M3 13h3v7H5a2 2 0 0 1-2-2zM21 13h-3v7h1a2 2 0 0 0 2-2zM18 20a6 6 0 0 1-6 2"/></svg>
      <span>DJ SESSIONS</span>
    </a>
    <a href="#morning-beats">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 19h18M5 16a7 7 0 0 1 14 0M12 2v3M4.9 6.9 7 9M2 13h3M19 13h3M17 9l2.1-2.1"/></svg>
      <span>MORNING BEATS</span>
    </a>
  </nav>
</section>

<section class="publico-experiencia">
  <span class="seccion-kicker">BLCK.CO</span>
  <h2>BLACK ON BLACK</h2>
  <p data-i18n="heroTagline">FOOD · COFFEE · EXPERIENCE</p>
</section>

<section class="morning-beats" id="morning-beats" aria-labelledby="morningBeatsTitle">
  <div class="morning-beats-contenido">
    <div class="morning-beats-copy" data-scroll-reveal>
      <span class="hero-kicker">SPECIALTY COFFEE · EST. BLCK.CO</span>
      <h2 id="morningBeatsTitle">
        <span>MORNING</span>
        <span>BEATS &amp;</span>
        <span>COFFEE</span>
      </h2>
      <p class="morning-beats-subtitulo">ELECTRONIC MUSIC · COFFEE · COMMUNITY</p>
      <p class="morning-beats-descripcion" data-i18n="morningDescription">
        Start the day differently. Curated electronic music, specialty coffee, meaningful connections and a community built around creativity, culture and wellness.
      </p>
      <a class="btn btn-acento morning-beats-cta" href="#morning-beats-details">
        <span data-i18n="exploreExperience">EXPLORE THE EXPERIENCE</span> <span aria-hidden="true">↗</span>
      </a>
    </div>
    <div class="morning-beats-edicion" data-scroll-reveal aria-hidden="true">
      <span>BLCK.CO</span>
      <span class="morning-beats-edicion-linea"></span>
      <span>DAY / 001</span>
    </div>
  </div>
  <div class="morning-beats-detalles" id="morning-beats-details">
    <div>
      <span class="morning-beats-numero">01</span>
      <span data-i18n="curatedHouse">CURATED HOUSE</span>
    </div>
    <div>
      <span class="morning-beats-numero">02</span>
      <span>SPECIALTY COFFEE</span>
    </div>
    <div>
      <span class="morning-beats-numero">03</span>
      <span data-i18n="creativeCommunity">CREATIVE COMMUNITY</span>
    </div>
  </div>
</section>

<section class="apparel-section" id="apparel" aria-labelledby="apparelTitulo">
  <div class="apparel-copy">
    <span class="seccion-kicker">BLCK.CO · FASHION &amp; LIFESTYLE</span>
    <h2 id="apparelTitulo">APPAREL</h2>
    <p class="apparel-manifesto">WEAR THE CULTURE.<br>MORE THAN COFFEE. A WAY OF LIFE.</p>
    <p class="apparel-descripcion" data-i18n="apparelDescription">
      Una futura línea urbana de BLCK.CO, concebida como parte de su universo creativo.
    </p>
    <ul class="apparel-categorias" aria-label="Líneas previstas" data-i18n-aria="upcomingLines">
      <li>Oversized T-shirts</li>
      <li>Hoodies</li>
      <li data-i18n="caps">Gorras</li>
      <li data-i18n="accessories">Accesorios</li>
    </ul>
    <p class="cultura-nota" data-i18n="apparelPending">Colección en preparación · catálogo y disponibilidad aún no publicados.</p>
  </div>
  <div class="apparel-marca" aria-hidden="true">
    <span>BLCK</span>
    <span class="apparel-marca-punto">.CO</span>
    <span class="apparel-marca-sub">APPAREL</span>
  </div>
</section>

<section class="cultura-lista" aria-label="Experiencias BLCK.CO" data-i18n-aria="brandExperiences">
  <article class="cultura-panel ink-panel" id="ink-lab">
    <span class="seccion-kicker">INK · ART · IDENTITY</span>
    <h2>INK LAB</h2>
    <p data-i18n="inkDescription">Un espacio para el arte del tatuaje y la expresión personal.</p>
    <div class="ink-explorador">
      <div class="ink-estilos" role="group" aria-label="Explorar estilos de tatuaje" data-i18n-aria="exploreTattooStyles">
        <button class="ink-estilo" type="button" data-ink-style="blackwork" aria-pressed="true">Blackwork</button>
        <button class="ink-estilo" type="button" data-ink-style="fine-line" aria-pressed="false">Fine line</button>
        <button class="ink-estilo" type="button" data-ink-style="minimalista" aria-pressed="false" data-i18n="minimalist">Minimalista</button>
        <button class="ink-estilo" type="button" data-ink-style="realismo" aria-pressed="false" data-i18n="realism">Realismo</button>
      </div>
      <p class="ink-descripcion" data-ink-description data-i18n="blackworkDescription" aria-live="polite">
        Contrastes marcados y composiciones en tinta negra. Explora el lenguaje visual del Blackwork.
      </p>
    </div>
    <span class="cultura-nota" data-i18n="inkPending">Explorador informativo; perfiles y citas, próximamente.</span>
  </article>

  <article class="cultura-panel dj-panel" id="dj-sessions">
    <span class="seccion-kicker">SOUND · CULTURE · NIGHT</span>
    <h2>DJ SESSIONS</h2>
    <p data-i18n="djDescription">Un punto de encuentro para la música y la cultura en BLCK.CO.</p>
    <div class="dj-visualizador" data-dj-visualizer aria-hidden="true">
      <?php for ($barra = 0; $barra < 28; $barra++): ?>
        <span class="dj-barra"></span>
      <?php endfor; ?>
    </div>
    <button class="btn btn-outline-light dj-control" type="button" data-dj-toggle aria-pressed="false" data-i18n="djActivate">
      Reproducir house original
    </button>
    <span class="cultura-nota" data-dj-status data-i18n="djInactive" aria-live="polite">House sintetizado en tiempo real · sin pistas ni samples de terceros.</span>
  </article>
</section>

<section class="visitanos" id="visitanos" aria-labelledby="visitanosTitulo">
  <div class="visitanos-contenido">
    <span class="seccion-kicker" data-i18n="visitKicker">BLCK.CO · SAMPER MENDOZA</span>
    <h2 id="visitanosTitulo" data-i18n="visitTitle">Visítanos</h2>
    <address data-i18n="visitAddress">Carrera 19 #23-14, Samper Mendoza</address>
    <p data-i18n="visitDays">Atención de martes a sábado.</p>
    <a class="btn btn-outline-light visit-map-link"
       href="https://www.google.com/maps/search/?api=1&amp;query=Carrera%2019%20%2323-14%2C%20Samper%20Mendoza"
       target="_blank" rel="noopener noreferrer" data-i18n="openMap">
      Abrir ubicación en Google Maps
    </a>
  </div>
</section>
