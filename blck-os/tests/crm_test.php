<?php
declare(strict_types=1);

use BlckOS\Controllers\CrmController;
use BlckOS\Core\Request;
use BlckOS\Core\Response;
use BlckOS\Middleware\CsrfMiddleware;
use BlckOS\Middleware\PermissionMiddleware;
use BlckOS\Models\CrmException;
use BlckOS\Models\CrmRepository;
use BlckOS\Models\RoleRepository;
use BlckOS\Models\AuditLogger;

require dirname(__DIR__) . '/bootstrap.php';

function crmExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$database = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$database->exec('PRAGMA foreign_keys = ON');
foreach ([
    'CREATE TABLE blck_os_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        description TEXT NOT NULL
    )',
    'CREATE TABLE blck_os_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        permission_key TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        description TEXT NOT NULL
    )',
    'CREATE TABLE blck_os_role_permissions (
        role_id INTEGER NOT NULL REFERENCES blck_os_roles(id),
        permission_id INTEGER NOT NULL REFERENCES blck_os_permissions(id),
        PRIMARY KEY (role_id, permission_id)
    )',
    'CREATE TABLE blck_os_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        is_active INTEGER NOT NULL DEFAULT 1,
        role_id INTEGER NOT NULL REFERENCES blck_os_roles(id)
    )',
    'CREATE TABLE blck_os_customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        phone TEXT NOT NULL DEFAULT \'\',
        status TEXT NOT NULL DEFAULT \'ACTIVE\',
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_customer_notes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL REFERENCES blck_os_customers(id) ON DELETE RESTRICT,
        note_text TEXT NOT NULL,
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_customer_memberships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL REFERENCES blck_os_customers(id) ON DELETE RESTRICT,
        membership_name TEXT NOT NULL,
        starts_on TEXT NOT NULL,
        ends_on TEXT NULL,
        status TEXT NOT NULL DEFAULT \'ACTIVE\',
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_customer_points (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL REFERENCES blck_os_customers(id) ON DELETE RESTRICT,
        points_delta INTEGER NOT NULL,
        entry_type TEXT NOT NULL,
        reason TEXT NOT NULL,
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
] as $statement) {
    $database->exec($statement);
}
$database->exec(
    'CREATE TABLE blck_os_audit_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        actor_user_id INTEGER NULL,
        action TEXT NOT NULL,
        entity_type TEXT NOT NULL,
        entity_id INTEGER NULL,
        details TEXT NOT NULL,
        occurred_at TEXT NOT NULL
    )'
);
$roleInsert = $database->prepare('INSERT INTO blck_os_roles (code, name, description) VALUES (?, ?, ?)');
foreach (['OWNER', 'EMPLEADO', 'CLIENTE'] as $roleCode) {
    $roleInsert->execute([$roleCode, $roleCode, 'Test role']);
}
$database->exec("INSERT INTO blck_os_permissions (permission_key, name, description) VALUES
    ('crm.view', 'View CRM', ''), ('crm.manage', 'Manage CRM', '')");
$database->exec(
    "INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id
     FROM blck_os_roles role_row CROSS JOIN blck_os_permissions permission_row
     WHERE role_row.code = 'OWNER'"
);
$database->exec(
    "INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id
     FROM blck_os_roles role_row
     JOIN blck_os_permissions permission_row ON permission_row.permission_key = 'crm.view'
     WHERE role_row.code = 'EMPLEADO'"
);
$database->exec("INSERT INTO blck_os_users (email, role_id) VALUES
    ('owner@example.test', 1), ('employee@example.test', 2), ('client@example.test', 3)");

$repository = new CrmRepository($database);
$roles = new RoleRepository($database);
$controller = new CrmController($repository, $roles, new AuditLogger($database));
$customerId = $repository->createCustomer('Ana Pérez', 'ANA@example.test', '+57 300 000 0000', 1);
crmExpect($repository->customer($customerId)['email'] === 'ana@example.test', 'Customer email should be normalized.');
crmExpect(count($repository->customers('Ana')) === 1, 'Customer name search failed.');

try {
    $repository->createCustomer('Otra Ana', 'ana@example.test', '', 1);
    crmExpect(false, 'Duplicate CRM emails must be rejected.');
} catch (CrmException $exception) {
    crmExpect(str_contains($exception->getMessage(), 'correo'), 'Duplicate email error should be explicit.');
}

$repository->updateCustomer($customerId, 'Ana María Pérez', 'ana@example.test', '3000000000');
crmExpect($repository->customer($customerId)['full_name'] === 'Ana María Pérez', 'Customer update failed.');
$repository->addNote($customerId, 'Conversación sobre la próxima sesión.', 1);
crmExpect(count($repository->notes($customerId)) === 1, 'CRM interaction history was not stored.');

$repository->assignMembership($customerId, 'Community', '2026-11-05', '2027-11-04', 1);
$repository->assignMembership($customerId, 'Founders', '2026-11-06', null, 1);
$memberships = $repository->memberships($customerId);
crmExpect(count($memberships) === 2, 'Membership history should preserve previous memberships.');
crmExpect($memberships[0]['membership_name'] === 'Founders' && $memberships[0]['status'] === 'ACTIVE', 'Newest membership should be active.');
crmExpect($memberships[1]['status'] === 'INACTIVE', 'Replacing a membership should retain the old one as inactive.');
try {
    $repository->assignMembership($customerId, 'Invalid dates', '2026-11-10', '2026-11-09', 1);
    crmExpect(false, 'Membership end dates must not precede their start dates.');
} catch (CrmException) {
}

$repository->recordPoints($customerId, 'EARN', 100, 'Bienvenida', 1);
$repository->recordPoints($customerId, 'REDEEM', 35, 'Beneficio de café', 1);
crmExpect((int) $repository->customer($customerId)['points_balance'] === 65, 'Loyalty point balance is incorrect.');
try {
    $repository->recordPoints($customerId, 'REDEEM', 66, 'Intento excedido', 1);
    crmExpect(false, 'Point redemption must not exceed the available balance.');
} catch (CrmException $exception) {
    crmExpect(str_contains($exception->getMessage(), 'suficientes'), 'Insufficient balance error should be explicit.');
}
crmExpect(count($repository->pointsHistory($customerId)) === 2, 'Rejected point redemption must not be stored.');

$repository->setCustomerActive($customerId, false);
crmExpect($repository->customer($customerId)['status'] === 'INACTIVE', 'Customer deactivation failed.');
$repository->setCustomerActive($customerId, true);
crmExpect($repository->customer($customerId)['status'] === 'ACTIVE', 'Customer reactivation failed.');

$_SESSION['csrf_token'] = str_repeat('c', 64);
$_SESSION['auth'] = ['id' => 1, 'email' => 'owner@example.test', 'auth_version' => 0];
$csrf = new CsrfMiddleware();
$rejectedPost = $csrf->process(
    new Request('POST', '/crm/notes', [], ['customer_id' => (string) $customerId, 'note_text' => 'Sin CSRF']),
    static fn (): Response => Response::html('unexpected')
);
crmExpect($rejectedPost->statusCode() === 403, 'CRM POST requests must require CSRF.');
$validPost = $csrf->process(
    new Request('POST', '/crm/notes', [], [
        'customer_id' => (string) $customerId,
        'note_text' => 'Nota protegida por CSRF',
        'csrf_token' => str_repeat('c', 64),
    ]),
    static fn (Request $request): Response => $controller->createNote($request)
);
crmExpect($validPost->statusCode() === 303, 'A valid CRM history note should redirect after saving.');
crmExpect($controller->index(new Request('GET', '/crm/customers'))->statusCode() === 200, 'CRM customer list should render.');
crmExpect($controller->detail(new Request('GET', '/crm/customer', ['id' => (string) $customerId]))->statusCode() === 200, 'CRM customer profile should render.');
crmExpect($controller->detail(new Request('GET', '/crm/customer', ['id' => '999']))->statusCode() === 404, 'Unknown CRM customers should return 404.');
crmExpect($controller->create(new Request('POST', '/crm/customer/new', [], [
    'full_name' => '',
    'email' => 'invalid',
]))->statusCode() === 422, 'Invalid CRM customer data should be rejected.');

$permissionMiddleware = new PermissionMiddleware(BLCKOS_CONFIG['route_permissions'], $roles);
$_SESSION['auth'] = ['id' => 2, 'email' => 'employee@example.test', 'auth_version' => 0];
crmExpect($permissionMiddleware->process(
    new Request('GET', '/crm/customers'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'An employee with crm.view should access CRM profiles.');
crmExpect($permissionMiddleware->process(
    new Request('POST', '/crm/notes'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'An employee without crm.manage must not change CRM records.');
$_SESSION['auth'] = ['id' => 3, 'email' => 'client@example.test', 'auth_version' => 0];
crmExpect($permissionMiddleware->process(
    new Request('GET', '/crm/customers'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'A CLIENTE role without crm.view must not access CRM.');

echo "CRM module tests passed." . PHP_EOL;
