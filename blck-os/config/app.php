<?php
declare(strict_types=1);

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDirectory = rtrim(dirname($scriptName), '/');
$basePath = basename($scriptDirectory) === 'public'
    ? dirname($scriptDirectory)
    : $scriptDirectory;
$configuredBaseUrl = getenv('BLCKOS_BASE_URL');
$publicUrl = getenv('BLCKOS_PUBLIC_URL');
$cookieSecureEnvironment = getenv('BLCKOS_COOKIE_SECURE');
$cookieSecure = is_string($cookieSecureEnvironment) && $cookieSecureEnvironment !== ''
    ? filter_var($cookieSecureEnvironment, FILTER_VALIDATE_BOOLEAN)
    : (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');

return [
    'name' => 'BLCK.OS',
    'timezone' => getenv('BLCKOS_TIMEZONE') ?: 'America/Bogota',
    'base_url' => is_string($configuredBaseUrl) && $configuredBaseUrl !== ''
        ? rtrim($configuredBaseUrl, '/')
        : rtrim($basePath, '/'),
    'public_url' => is_string($publicUrl) ? rtrim($publicUrl, '/') : '',
    'session_idle_timeout' => 1800,
    'session_absolute_timeout' => 28800,
    'session_cookie_secure' => $cookieSecure,
    'session_cookie_name' => 'BLCKOSSESSID',
    'password_bcrypt_cost' => 12,
    'password_reset_ttl_minutes' => 60,
    'password_reset_cooldown_seconds' => 60,
    'route_permissions' => [
        '/dashboard' => 'system.access',
        '/auth/change-password' => 'account.password.change',
        'GET /users' => 'users.view',
        'GET /users/create' => 'users.manage',
        'POST /users/create' => 'users.manage',
        'GET /users/edit' => 'users.manage',
        'POST /users/edit' => 'users.manage',
        'POST /users/activate' => 'users.manage',
        'POST /users/deactivate' => 'users.manage',
        'POST /users/delete' => 'users.manage',
        'GET /inventory' => 'inventory.view',
        'POST /inventory/categories' => 'inventory.manage',
        'POST /inventory/subcategories' => 'inventory.manage',
        'POST /inventory/products' => 'inventory.manage',
        'GET /inventory/products/edit' => 'inventory.manage',
        'POST /inventory/products/edit' => 'inventory.manage',
        'POST /inventory/movements' => 'inventory.manage',
        'GET /sales' => 'sales.view',
        'GET /sales/new' => 'sales.manage',
        'POST /sales/create' => 'sales.manage',
        'GET /sales/ticket' => 'sales.view',
        'GET /events' => 'events.view',
        'GET /events/new' => 'events.manage',
        'POST /events/create' => 'events.manage',
        'GET /events/edit' => 'events.manage',
        'POST /events/edit' => 'events.manage',
        'GET /events/detail' => 'events.manage',
        'POST /events/reservations' => 'events.manage',
        'GET /events/reservation' => 'events.manage',
        'GET /events/check-in' => 'events.manage',
        'POST /events/check-in' => 'events.manage',
        'GET /crm/customers' => 'crm.view',
        'GET /crm/customer' => 'crm.view',
        'GET /crm/customer/new' => 'crm.manage',
        'POST /crm/customer/new' => 'crm.manage',
        'GET /crm/customer/edit' => 'crm.manage',
        'POST /crm/customer/edit' => 'crm.manage',
        'POST /crm/customer/status' => 'crm.manage',
        'POST /crm/notes' => 'crm.manage',
        'POST /crm/memberships' => 'crm.manage',
        'POST /crm/points' => 'crm.manage',
        'GET /reports' => 'reports.view',
        'GET /audit' => 'audit.view',
    ],
    'smtp' => [
        'host' => getenv('BLCKOS_SMTP_HOST') ?: '',
        'port' => getenv('BLCKOS_SMTP_PORT') ?: '587',
        'username' => getenv('BLCKOS_SMTP_USERNAME') ?: '',
        'password' => getenv('BLCKOS_SMTP_PASSWORD') ?: '',
        'encryption' => strtolower(getenv('BLCKOS_SMTP_ENCRYPTION') ?: 'tls'),
        'from_address' => getenv('BLCKOS_SMTP_FROM_ADDRESS') ?: '',
        'from_name' => getenv('BLCKOS_SMTP_FROM_NAME') ?: 'BLCK.OS',
    ],
];
