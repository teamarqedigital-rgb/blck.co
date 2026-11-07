<?php
declare(strict_types=1);

use BlckOS\Core\Router;
use BlckOS\Middleware\AuthMiddleware;
use BlckOS\Middleware\CsrfMiddleware;
use BlckOS\Middleware\PermissionMiddleware;
use BlckOS\Middleware\SessionMiddleware;

$appConfig = require __DIR__ . '/config/app.php';
define('BLCKOS_CONFIG', $appConfig);

date_default_timezone_set($appConfig['timezone']);

$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'BlckOS\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/app/Helpers/helpers.php';

$router = new Router();
$router->middleware(new SessionMiddleware($appConfig));
$router->middleware(new CsrfMiddleware());
$router->middleware(new AuthMiddleware());
$router->middleware(new PermissionMiddleware($appConfig['route_permissions']));

return $router;
