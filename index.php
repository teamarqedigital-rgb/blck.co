<?php
// Front controller / router de blck.co
// URL: index.php?r=controlador/accion&id=1
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

spl_autoload_register(function ($clase) {
    $f = __DIR__ . "/{$clase}.php";
    if (is_file($f)) {
        require $f;
    }
});

$ruta = $_GET['r'] ?? 'menu/index';
if (!preg_match('/^[a-z]+\/[a-z]+$/', $ruta)) {
    $ruta = 'menu/index';
}
[$ctrl, $accion] = explode('/', $ruta);
$clase = ucfirst($ctrl) . 'Controller';

try {
    if (!class_exists($clase) || !method_exists($clase, $accion)) {
        http_response_code(404);
        exit('Página no encontrada');
    }
    (new $clase())->$accion();
} catch (PDOException $ex) {
    http_response_code(500);
    error_log($ex->getMessage());
    exit('Error de base de datos. Revisa config.php y confirma que MySQL esté iniciado.');
}
