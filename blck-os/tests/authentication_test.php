<?php
declare(strict_types=1);

use BlckOS\Controllers\AuthController;
use BlckOS\Controllers\DashboardController;
use BlckOS\Controllers\PasswordController;
use BlckOS\Controllers\UserController;
use BlckOS\Core\Request;
use BlckOS\Core\Response;
use BlckOS\Core\SessionManager;
use BlckOS\Core\View;
use BlckOS\Middleware\AuthMiddleware;
use BlckOS\Middleware\CsrfMiddleware;
use BlckOS\Models\UserRepository;
use BlckOS\Models\DashboardRepository;
use BlckOS\Models\UserManagementException;
use BlckOS\Models\UserManagementRepository;
use BlckOS\Models\AuditLogger;

require dirname(__DIR__) . '/bootstrap.php';

function expect(bool $condition, string $message): void
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
$database->exec(
    'CREATE TABLE blck_os_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        description TEXT NOT NULL
    )'
);
$database->exec(
    'CREATE TABLE blck_os_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        permission_key TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        description TEXT NOT NULL
    )'
);
$database->exec(
    'CREATE TABLE blck_os_role_permissions (
        role_id INTEGER NOT NULL REFERENCES blck_os_roles(id) ON DELETE CASCADE,
        permission_id INTEGER NOT NULL REFERENCES blck_os_permissions(id) ON DELETE CASCADE,
        PRIMARY KEY (role_id, permission_id)
    )'
);
$roleInsert = $database->prepare('INSERT INTO blck_os_roles (code, name, description) VALUES (?, ?, ?)');
foreach (['OWNER', 'ADMINISTRADOR', 'EMPLEADO', 'CLIENTE'] as $roleCode) {
    $roleInsert->execute([$roleCode, $roleCode, 'Test role']);
}
$permissionKeys = [
    'system.access',
    'account.password.change',
    'users.view',
    'users.manage',
    'roles.manage',
    'inventory.view',
    'inventory.manage',
    'sales.view',
    'sales.manage',
    'events.view',
    'events.manage',
    'crm.view',
    'crm.manage',
    'reports.view',
    'audit.view',
];
$permissionInsert = $database->prepare(
    'INSERT INTO blck_os_permissions (permission_key, name, description) VALUES (?, ?, ?)'
);
foreach ($permissionKeys as $permissionKey) {
    $permissionInsert->execute([$permissionKey, $permissionKey, 'Test permission']);
}
$grant = $database->prepare(
    'INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id
     FROM blck_os_roles role_row CROSS JOIN blck_os_permissions permission_row
     WHERE role_row.code = ?'
);
$grant->execute(['OWNER']);
foreach ([
    'ADMINISTRADOR' => array_diff($permissionKeys, ['roles.manage']),
    'EMPLEADO' => [
        'system.access',
        'account.password.change',
        'inventory.view',
        'sales.view',
        'sales.manage',
        'events.view',
        'events.manage',
        'crm.view',
    ],
    'CLIENTE' => ['system.access', 'account.password.change', 'events.view'],
] as $roleCode => $rolePermissions) {
    $grantPermissions = $database->prepare(
        'INSERT INTO blck_os_role_permissions (role_id, permission_id)
         SELECT role_row.id, permission_row.id
         FROM blck_os_roles role_row CROSS JOIN blck_os_permissions permission_row
         WHERE role_row.code = ? AND permission_row.permission_key = ?'
    );
    foreach ($rolePermissions as $permissionKey) {
        $grantPermissions->execute([$roleCode, $permissionKey]);
    }
}
$database->exec(
    'CREATE TABLE blck_os_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        auth_version INTEGER NOT NULL DEFAULT 0,
        role_id INTEGER NULL REFERENCES blck_os_roles(id) ON DELETE RESTRICT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$database->exec(
    'CREATE TABLE blck_os_password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL REFERENCES blck_os_users(id) ON DELETE CASCADE,
        token_hash TEXT NOT NULL UNIQUE,
        expires_at TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        used_at TEXT NULL
    )'
);
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

$passwordHash = password_hash('correct-horse-battery-staple', PASSWORD_BCRYPT, ['cost' => 4]);
$insert = $database->prepare('INSERT INTO blck_os_users (email, password_hash) VALUES (?, ?)');
$insert->execute(['owner@example.test', $passwordHash]);
$userId = (int) $database->lastInsertId();
$users = new UserRepository($database);
$roles = new \BlckOS\Models\RoleRepository($database);
$managedUsers = new UserManagementRepository($database);
$audit = new AuditLogger($database);
$ownerPermissions = $permissionKeys;
sort($ownerPermissions);

expect($users->findActiveByEmail('owner@example.test')['id'] === $userId, 'Active email lookup failed.');
expect($users->findActiveByEmail('missing@example.test') === null, 'Unknown email should not resolve.');
expect($roles->permissionsForRole('OWNER') === $ownerPermissions, 'OWNER must receive every defined permission.');
expect(in_array('roles.manage', $roles->permissionsForRole('ADMINISTRADOR'), true) === false, 'ADMINISTRADOR must not manage roles.');
expect(in_array('sales.manage', $roles->permissionsForRole('EMPLEADO'), true), 'EMPLEADO should have operational sales access.');
expect($roles->assignRole($userId, 'EMPLEADO'), 'A valid role could not be assigned to a user.');
expect($roles->userHasPermission($userId, 'sales.manage'), 'An assigned employee permission was not resolved.');
expect(!$roles->userHasPermission($userId, 'users.manage'), 'An employee must not manage users.');

$managedUserId = $managedUsers->create(
    'created@example.test',
    password_hash('created-user-password', PASSWORD_BCRYPT, ['cost' => 4]),
    'EMPLEADO'
);
expect($managedUsers->findById($managedUserId)['email'] === 'created@example.test', 'User creation failed.');
expect($managedUsers->emailExists('created@example.test'), 'New user email was not indexed for lookup.');
$managedUsers->update($managedUserId, 'updated@example.test', 'CLIENTE');
$updatedManagedUser = $managedUsers->findById($managedUserId);
expect($updatedManagedUser['email'] === 'updated@example.test', 'Updating a user email failed.');
expect($updatedManagedUser['role_code'] === 'CLIENTE', 'Updating a user role failed.');
expect((int) $database->query(
    'SELECT auth_version FROM blck_os_users WHERE id = ' . $managedUserId
)->fetchColumn() === 1, 'Changing user credentials or role must revoke previous sessions.');
$versionBeforeDeactivation = (int) $database->query(
    'SELECT auth_version FROM blck_os_users WHERE id = ' . $managedUserId
)->fetchColumn();
$managedUsers->setActive($managedUserId, false);
expect((int) $managedUsers->findById($managedUserId)['is_active'] === 0, 'Deactivating a user failed.');
expect((int) $database->query(
    'SELECT auth_version FROM blck_os_users WHERE id = ' . $managedUserId
)->fetchColumn() === $versionBeforeDeactivation + 1, 'Deactivation did not revoke prior sessions.');
$inactiveVersion = (int) $database->query(
    'SELECT auth_version FROM blck_os_users WHERE id = ' . $managedUserId
)->fetchColumn();
$managedUsers->setActive($managedUserId, true);
expect((int) $managedUsers->findById($managedUserId)['is_active'] === 1, 'Activating a user failed.');
expect((int) $database->query(
    'SELECT auth_version FROM blck_os_users WHERE id = ' . $managedUserId
)->fetchColumn() === $inactiveVersion + 1, 'Reactivation did not revoke stale sessions.');
$managedUsers->delete($managedUserId);
expect($managedUsers->findById($managedUserId) === null, 'User deletion failed.');

$soleOwnerId = $managedUsers->create(
    'sole-owner@example.test',
    password_hash('sole-owner-password', PASSWORD_BCRYPT, ['cost' => 4]),
    'OWNER'
);
foreach ([
    static fn () => $managedUsers->setActive($soleOwnerId, false),
    static fn () => $managedUsers->update($soleOwnerId, 'sole-owner@example.test', 'EMPLEADO'),
    static fn () => $managedUsers->delete($soleOwnerId),
] as $removeSoleOwner) {
    try {
        $removeSoleOwner();
        expect(false, 'The last active OWNER must not be deactivated, demoted, or deleted.');
    } catch (UserManagementException $exception) {
        expect(str_contains($exception->getMessage(), 'último OWNER'), 'The OWNER safety error should explain the restriction.');
    }
}
$backupOwnerId = $managedUsers->create(
    'backup-owner@example.test',
    password_hash('backup-owner-password', PASSWORD_BCRYPT, ['cost' => 4]),
    'OWNER'
);
$managedUsers->setActive($soleOwnerId, false);
$managedUsers->delete($soleOwnerId);
try {
    $managedUsers->delete($backupOwnerId);
    expect(false, 'The remaining active OWNER must be protected.');
} catch (UserManagementException $exception) {
    expect(str_contains($exception->getMessage(), 'último OWNER'), 'The remaining OWNER should be protected from deletion.');
}

$rawToken = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $rawToken);
$utc = new DateTimeZone('UTC');
$expiresAt = (new DateTimeImmutable('+60 minutes', $utc))->format('Y-m-d H:i:s');
expect($users->issuePasswordReset($userId, $tokenHash, $expiresAt), 'New reset request was not stored.');
expect(!$users->issuePasswordReset($userId, hash('sha256', random_bytes(32)), $expiresAt), 'Reset cooldown was not enforced.');
expect($users->hasValidPasswordReset($tokenHash), 'New reset token was not found.');
$storedHash = $database->query('SELECT token_hash FROM blck_os_password_resets')->fetchColumn();
expect($storedHash === $tokenHash && $storedHash !== $rawToken, 'Reset tokens must be stored as SHA-256 hashes.');

$replacementHash = password_hash('new-strong-passphrase', PASSWORD_BCRYPT, ['cost' => 4]);
$resetUser = $users->completePasswordReset($tokenHash, $replacementHash);
expect($resetUser !== null && (int) $resetUser['auth_version'] === 1, 'Password reset did not invalidate prior sessions.');
expect(password_verify('new-strong-passphrase', $resetUser['password_hash']), 'Reset password hash was not saved.');
expect(!$users->hasValidPasswordReset($tokenHash), 'A consumed reset token must not remain valid.');
expect($users->completePasswordReset($tokenHash, $replacementHash) === null, 'A reset token was accepted more than once.');

$changedUser = $users->changePassword($userId, password_hash('changed-strong-passphrase', PASSWORD_BCRYPT, ['cost' => 4]));
expect($changedUser !== null && (int) $changedUser['auth_version'] === 2, 'Changing a password did not invalidate other sessions.');

$secondUser = $database->prepare('INSERT INTO blck_os_users (email, password_hash) VALUES (?, ?)');
$secondUser->execute(['inactive@example.test', $passwordHash]);
$inactiveId = (int) $database->lastInsertId();
$roles->assignRole($inactiveId, 'ADMINISTRADOR');
$database->prepare('UPDATE blck_os_users SET is_active = 0 WHERE id = ?')->execute([$inactiveId]);
expect($users->findActiveByEmail('inactive@example.test') === null, 'Inactive users must not be eligible for authentication.');
expect(!$roles->userHasPermission($inactiveId, 'users.manage'), 'Inactive users must not retain effective permissions.');

$clientInsert = $database->prepare('INSERT INTO blck_os_users (email, password_hash) VALUES (?, ?)');
$clientInsert->execute(['client@example.test', $passwordHash]);
$clientId = (int) $database->lastInsertId();
expect($roles->assignRole($clientId, 'CLIENTE'), 'The client role could not be assigned.');
$dashboardMetrics = (new DashboardRepository($database))->summary();
expect($dashboardMetrics['total_users'] === 4, 'Dashboard user totals must use the user repository data.');
expect($dashboardMetrics['total_clients'] === 1, 'Dashboard client totals must count client-role accounts.');
expect($dashboardMetrics['active_events'] === 3, 'Dashboard event demo metric is missing.');
expect($dashboardMetrics['sales_today'] === 1280000, 'Dashboard sales demo metric is missing.');
expect(in_array('active_events', $dashboardMetrics['simulated_metrics'], true), 'Simulated event figures must be identified.');
expect(in_array('sales_today', $dashboardMetrics['simulated_metrics'], true), 'Simulated sales figures must be identified.');

$expiredToken = hash('sha256', random_bytes(32));
$database->prepare(
    'INSERT INTO blck_os_password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
)->execute([$userId, $expiredToken, (new DateTimeImmutable('-1 minute'))->format('Y-m-d H:i:s')]);
expect(!$users->hasValidPasswordReset($expiredToken), 'Expired reset tokens must be rejected.');
expect($users->completePasswordReset($expiredToken, $replacementHash) === null, 'An expired reset token was accepted.');

expect(PasswordController::passwordValidationError('12345678901', '12345678901') !== null, 'Short passwords must be rejected.');
expect(PasswordController::passwordValidationError('valid-passphrase', 'different-passphrase') !== null, 'Mismatched passwords must be rejected.');
expect(PasswordController::passwordValidationError(str_repeat('é', 12), str_repeat('é', 12)) === null, 'Twelve Unicode characters should be accepted.');
expect(PasswordController::passwordValidationError(str_repeat('é', 37), str_repeat('é', 37)) !== null, 'Passwords over bcrypt byte limits must be rejected.');
expect(class_exists(\PHPMailer\PHPMailer\PHPMailer::class), 'The isolated SMTP dependency must be available.');

$dummyHash = '$2y$12$B7/5M8DfWk15U7yZHq8Xy.3DCV8wq3ICG3XOGwN3X9cGc2d2FM1Iu';
expect(password_get_info($dummyHash)['algoName'] === 'bcrypt', 'The timing-safe login fallback must be a valid bcrypt hash.');
expect(!password_verify('owner-password', $dummyHash), 'The login fallback hash must not match an account password.');

SessionManager::start(BLCKOS_CONFIG);
$csrf = \BlckOS\Helpers\csrf_token();
$permissionMiddleware = new \BlckOS\Middleware\PermissionMiddleware([
    '/dashboard' => 'system.access',
    '/auth/change-password' => 'account.password.change',
    'GET /users' => 'users.view',
    'POST /users' => 'users.manage',
    'GET /users/create' => 'users.manage',
    'POST /users/create' => 'users.manage',
    'POST /users/delete' => 'users.manage',
    'GET /inventory' => 'inventory.view',
    'POST /inventory/categories' => 'inventory.manage',
    'GET /events' => 'events.view',
    'GET /events/new' => 'events.manage',
    'POST /events/check-in' => 'events.manage',
], $roles);
expect($permissionMiddleware->process(
    new Request('GET', '/users'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 303, 'Unauthenticated access to a permission-protected route must redirect to login.');
$csrfMiddleware = new CsrfMiddleware();
$nextCalled = false;
$invalidCsrf = $csrfMiddleware->process(
    new Request('POST', '/auth/login', [], ['csrf_token' => 'invalid']),
    static function () use (&$nextCalled): Response {
        $nextCalled = true;
        return Response::html('unexpected');
    }
);
expect($invalidCsrf->statusCode() === 403 && !$nextCalled, 'Invalid CSRF submissions must be rejected before controllers run.');

$preAuthSession = session_id();
$loginResponse = $csrfMiddleware->process(
    new Request('POST', '/auth/login', [], [
        'csrf_token' => $csrf,
        'email' => 'owner@example.test',
        'password' => 'changed-strong-passphrase',
    ]),
    static fn (Request $request): Response => (new AuthController($users, $audit))->login($request)
);
expect($loginResponse->statusCode() === 303, 'A valid login should redirect after authentication.');
expect(session_id() !== $preAuthSession, 'Login must rotate the session identifier.');
expect(SessionManager::user()['id'] === $userId, 'Login did not establish the authenticated session.');
expect(!array_key_exists('csrf_token', SessionManager::user()), 'CSRF token must not be stored in the authentication identity.');
expect(strlen($_SESSION['csrf_token'] ?? '') === 64, 'Login must rotate the CSRF token.');

$deniedRouteCalled = false;
$deniedUserManagement = $permissionMiddleware->process(
    new Request('GET', '/users'),
    static function () use (&$deniedRouteCalled): Response {
        $deniedRouteCalled = true;
        return Response::html('unexpected');
    }
);
expect($deniedUserManagement->statusCode() === 403 && !$deniedRouteCalled, 'A route without its required permission must be denied.');
expect($permissionMiddleware->process(
    new Request('GET', '/auth/change-password'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'An employee should retain access to their own password change.');
expect($permissionMiddleware->process(
    new Request('GET', '/dashboard'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'A permitted employee should access the dashboard.');
expect($permissionMiddleware->process(
    new Request('GET', '/inventory'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'An employee with inventory.view should access the inventory list.');
$deniedInventoryWrite = $permissionMiddleware->process(
    new Request('POST', '/inventory/categories'),
    static fn (): Response => Response::html('unexpected')
);
expect($deniedInventoryWrite->statusCode() === 403, 'An employee without inventory.manage must not write inventory data.');
expect($permissionMiddleware->process(
    new Request('GET', '/events'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'A user with events.view should access the published event listing.');
expect($permissionMiddleware->process(
    new Request('GET', '/events/new'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'An employee with events.manage should be allowed to create events.');
expect($roles->assignRole($userId, 'CLIENTE'), 'The CLIENTE role could not be assigned for permission checks.');
expect($permissionMiddleware->process(
    new Request('GET', '/events/new'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'A user without events.manage must not create events.');
expect($permissionMiddleware->process(
    new Request('POST', '/events/check-in'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'A user without events.manage must not validate event QR codes.');
expect($roles->assignRole($userId, 'EMPLEADO'), 'The EMPLEADO role could not be restored after permission checks.');
expect((new DashboardController(new DashboardRepository($database), $roles))
    ->index(new Request('GET', '/dashboard'))
    ->statusCode() === 200, 'The dashboard controller should render a successful response.');
$dashboardHtml = View::render('dashboard/index', [
    'title' => 'Panel administrativo',
    'user' => SessionManager::user(),
    'metrics' => $dashboardMetrics,
    'currentDate' => '05/11/2026',
    'canViewUsers' => true,
    'canViewInventory' => true,
    'canViewSales' => true,
    'canViewEvents' => true,
]);
foreach (['Resumen general', 'Total usuarios', 'Total clientes', 'Eventos activos', 'Ventas del día', '1.280.000', 'Indicador simulado'] as $label) {
    expect(str_contains($dashboardHtml, $label), 'The dashboard is missing the expected display: ' . $label);
}
expect($roles->assignRole($userId, 'OWNER'), 'The OWNER role could not be assigned.');
expect($permissionMiddleware->process(
    new Request('GET', '/users'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'A role with the required permission should pass authorization.');
expect($permissionMiddleware->process(
    new Request('POST', '/inventory/categories'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'An owner with inventory.manage should be allowed to write inventory data.');
$userController = new UserController($managedUsers, $roles, $audit);
expect($userController->index(new Request('GET', '/users'))->statusCode() === 200, 'The user list should render.');
expect($userController->create(new Request('GET', '/users/create'))->statusCode() === 200, 'The create-user form should render.');
$formHtml = View::render('users/form', [
    'user' => null,
    'email' => '',
    'roles' => $managedUsers->roles(),
    'canManageRoles' => true,
    'error' => null,
]);
foreach (['CREAR USUARIO', 'name="csrf_token"', 'name="role_code"', 'name="password_confirmation"'] as $formElement) {
    expect(str_contains($formHtml, $formElement), 'The user form is missing a required field: ' . $formElement);
}
$roles->assignRole($userId, 'ADMINISTRADOR');
$roleTampering = $userController->create(new Request('POST', '/users/create', [], [
    'email' => 'forbidden-role@example.test',
    'password' => 'long-secure-admin-password',
    'password_confirmation' => 'long-secure-admin-password',
    'role_code' => 'OWNER',
]));
expect($roleTampering->statusCode() === 403, 'Administrators must not grant roles they do not manage.');
$roles->assignRole($userId, 'OWNER');
$createRequest = new Request('POST', '/users/create', [], [
    'csrf_token' => $_SESSION['csrf_token'],
    'email' => 'controller-created@example.test',
    'password' => 'long-secure-user-password',
    'password_confirmation' => 'long-secure-user-password',
]);
$createResponse = $permissionMiddleware->process(
    $createRequest,
    static fn (Request $request): Response => $csrfMiddleware->process(
        $request,
        static fn (Request $csrfRequest): Response => $userController->create($csrfRequest)
    )
);
expect($createResponse->statusCode() === 303, 'A permitted, CSRF-protected user creation should redirect.');
expect($managedUsers->emailExists('controller-created@example.test'), 'The create-user route did not persist its new user.');
expect($userController->create(new Request('POST', '/users/create', [], [
    'email' => 'invalid-password@example.test',
    'password' => 'short',
    'password_confirmation' => 'short',
]))->statusCode() === 422, 'Invalid new-user credentials must be rejected.');
expect($userController->delete(new Request('POST', '/users/delete', [], [
    'id' => (string) $userId,
]))->statusCode() === 422, 'An owner must not be able to delete their own account.');
expect($roles->assignRole($userId, 'CLIENTE'), 'The CLIENTE role could not be assigned.');
expect(!$roles->userHasPermission($userId, 'users.manage'), 'CLIENTE must not inherit administrative permissions.');
expect($roles->assignRole($userId, 'EMPLEADO'), 'The employee role could not be restored for authentication tests.');

$authMiddleware = new AuthMiddleware($users);
$protectedResponse = $authMiddleware->process(
    new Request('GET', '/auth/change-password'),
    static fn (): Response => Response::html('protected')
);
expect($protectedResponse->statusCode() === 200, 'An active authenticated user should pass authorization.');

$changeResponse = $csrfMiddleware->process(
    new Request('POST', '/auth/change-password', [], [
        'csrf_token' => $_SESSION['csrf_token'],
        'current_password' => 'changed-strong-passphrase',
        'new_password' => 'another-strong-passphrase',
        'new_password_confirmation' => 'another-strong-passphrase',
    ]),
    static fn (Request $request): Response => (new PasswordController($users, $audit))->change($request)
);
expect($changeResponse->statusCode() === 200, 'Changing the password with a valid current password should succeed.');
expect(SessionManager::user()['auth_version'] === 3, 'Password change should refresh the current authentication version.');

$_SESSION['auth']['auth_version'] = 2;
$revokedResponse = $authMiddleware->process(
    new Request('GET', '/auth/change-password'),
    static fn (): Response => Response::html('unexpected')
);
expect($revokedResponse->statusCode() === 303, 'A stale authentication version must be revoked.');
expect(SessionManager::user() === null, 'A revoked session must be cleared.');
expect($authMiddleware->process(
    new Request('GET', '/auth/change-password'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 303, 'An unauthenticated user must not access password changes.');

SessionManager::start(BLCKOS_CONFIG);
SessionManager::login($users->findActiveById($userId));
$logoutResponse = $csrfMiddleware->process(
    new Request('POST', '/auth/logout', [], ['csrf_token' => $_SESSION['csrf_token']]),
    static fn (Request $request): Response => (new AuthController($users, $audit))->logout($request)
);
expect($logoutResponse->statusCode() === 303, 'Logout should redirect after clearing the session.');
expect(SessionManager::user() === null, 'Logout must clear the authenticated session.');

fwrite(STDOUT, "Authentication repository tests passed.\n");