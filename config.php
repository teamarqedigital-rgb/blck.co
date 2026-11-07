<?php
// Configuración general de blck.co
// Los valores de conexión pueden sobrescribirse en database.local.php (ignorado por Git).
$db = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'blckco',
    'user' => 'root',
    'pass' => ''
];
$local = __DIR__ . '/database.local.php';
if (file_exists($local)) {
    $db = array_merge($db, require $local);
}
define('DB_CONFIG', $db);

define('APP_NAME', 'blck.co');
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'));
define('STOCK_BAJO', 10); // umbral para alertas de inventario
