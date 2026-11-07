<?php
declare(strict_types=1);

use BlckOS\Core\Request;

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $assetRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'assets');
    $requestedFile = is_string($requestPath)
        ? realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($requestPath, '/\\'))
        : false;

    if (
        $assetRoot !== false
        && $requestedFile !== false
        && is_file($requestedFile)
        && str_starts_with($requestedFile, $assetRoot . DIRECTORY_SEPARATOR)
    ) {
        return false;
    }
}

$router = require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/routes/web.php';

$response = $router->dispatch(Request::fromGlobals());
$response->send();
