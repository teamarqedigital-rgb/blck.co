<?php
declare(strict_types=1);

use BlckOS\Controllers\ReportController;
use BlckOS\Core\Request;
use BlckOS\Core\Response;
use BlckOS\Middleware\PermissionMiddleware;
use BlckOS\Models\ReportRepository;
use BlckOS\Models\RoleRepository;

require dirname(__DIR__) . '/bootstrap.php';

function reportsExpect(bool $condition, string $message): void
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
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        PRIMARY KEY (role_id, permission_id)
    )',
    'CREATE TABLE blck_os_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        role_id INTEGER NOT NULL
    )',
    'CREATE TABLE blck_os_inventory_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL
    )',
    'CREATE TABLE blck_os_inventory_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sku TEXT NOT NULL,
        name TEXT NOT NULL,
        category_id INTEGER NOT NULL,
        unit_price NUMERIC NOT NULL,
        stock_quantity INTEGER NOT NULL
    )',
    'CREATE TABLE blck_os_inventory_movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        movement_type TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        stock_before INTEGER NOT NULL,
        stock_after INTEGER NOT NULL,
        note TEXT NOT NULL,
        created_by INTEGER NULL,
        created_at TEXT NOT NULL
    )',
    'CREATE TABLE blck_os_sales (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        total NUMERIC NOT NULL,
        created_at TEXT NOT NULL
    )',
    'CREATE TABLE blck_os_sale_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sale_id INTEGER NOT NULL,
        sku TEXT NOT NULL,
        product_name TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        line_total NUMERIC NOT NULL
    )',
    'CREATE TABLE blck_os_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sale_id INTEGER NOT NULL,
        payment_method TEXT NOT NULL,
        amount NUMERIC NOT NULL
    )',
    'CREATE TABLE blck_os_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        event_type TEXT NOT NULL,
        status TEXT NOT NULL,
        starts_at TEXT NOT NULL,
        capacity INTEGER NOT NULL
    )',
    'CREATE TABLE blck_os_event_reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        status TEXT NOT NULL,
        guest_count INTEGER NOT NULL
    )',
    'CREATE TABLE blck_os_customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        status TEXT NOT NULL,
        created_at TEXT NOT NULL
    )',
    'CREATE TABLE blck_os_customer_memberships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        status TEXT NOT NULL,
        starts_on TEXT NOT NULL,
        ends_on TEXT NULL
    )',
    'CREATE TABLE blck_os_customer_points (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        points_delta INTEGER NOT NULL,
        created_at TEXT NOT NULL
    )',
] as $statement) {
    $database->exec($statement);
}

$database->exec("INSERT INTO blck_os_roles (code, name, description)
    VALUES ('OWNER', 'Owner', ''), ('EMPLEADO', 'Employee', '')");
$database->exec("INSERT INTO blck_os_permissions (permission_key, name, description)
    VALUES ('reports.view', 'View reports', '')");
$database->exec("INSERT INTO blck_os_role_permissions (role_id, permission_id) VALUES (1, 1)");
$database->exec("INSERT INTO blck_os_users (email, role_id)
    VALUES ('owner@example.test', 1), ('employee@example.test', 2)");
$database->exec("INSERT INTO blck_os_inventory_categories (name) VALUES ('Coffee')");
$database->exec("INSERT INTO blck_os_inventory_products (sku, name, category_id, unit_price, stock_quantity)
    VALUES ('BEAN-01', 'House Blend', 1, 1000, 5)");
$database->exec("INSERT INTO blck_os_inventory_movements
    (product_id, movement_type, quantity, stock_before, stock_after, note, created_by, created_at)
    VALUES (1, 'IN', 5, 0, 5, 'Opening stock', 1, '2026-11-02 09:00:00'),
           (1, 'OUT', -1, 5, 4, 'Outside range', 1, '2026-11-08 00:00:00')");
$database->exec("INSERT INTO blck_os_sales (total, created_at)
    VALUES (2500, '2026-11-02 10:00:00'), (9000, '2026-11-08 00:00:00')");
$database->exec("INSERT INTO blck_os_sale_items
    (sale_id, sku, product_name, quantity, line_total)
    VALUES (1, 'BEAN-01', 'House Blend', 2, 2000), (2, 'BEAN-01', 'House Blend', 9, 9000)");
$database->exec("INSERT INTO blck_os_payments (sale_id, payment_method, amount)
    VALUES (1, 'CASH', 2500), (2, 'CARD', 9000)");
$database->exec("INSERT INTO blck_os_events (title, event_type, status, starts_at, capacity)
    VALUES ('Morning Session', 'MORNING_BEATS_COFFEE', 'PUBLISHED', '2026-11-03 14:00:00', 50),
           ('Outside range', 'DJ_SESSION', 'PUBLISHED', '2026-11-08 00:00:00', 20)");
$database->exec("INSERT INTO blck_os_event_reservations (event_id, status, guest_count)
    VALUES (1, 'CONFIRMED', 2), (1, 'CHECKED_IN', 1), (2, 'CONFIRMED', 9)");
$database->exec("INSERT INTO blck_os_customers (full_name, email, status, created_at)
    VALUES ('Ana Pérez', 'ana@example.test', 'ACTIVE', '2026-11-02 08:00:00'),
           ('Outside range', 'outside@example.test', 'ACTIVE', '2026-11-08 00:00:00')");
$database->exec("INSERT INTO blck_os_customer_memberships (customer_id, status, starts_on, ends_on)
    VALUES (1, 'ACTIVE', '2000-01-01', NULL)");
$database->exec("INSERT INTO blck_os_customer_points (customer_id, points_delta, created_at)
    VALUES (1, 100, '2026-11-02 12:00:00'), (1, -25, '2026-11-03 12:00:00'), (1, 900, '2026-11-08 00:00:00')");

$reports = new ReportRepository($database);
$from = '2026-11-01';
$untilExclusive = '2026-11-08';
$sales = $reports->sales($from, $untilExclusive);
reportsExpect((int) $sales['summary']['sale_count'] === 1, 'Sales report did not apply inclusive date bounds.');
reportsExpect((float) $sales['summary']['sales_total'] === 2500.0, 'Sales report total is incorrect.');
reportsExpect(count($sales['by_day']) === 1, 'Daily sales aggregation is incorrect.');
reportsExpect($sales['top_products'][0]['product_name'] === 'House Blend', 'Top product aggregation is incorrect.');

$inventory = $reports->inventory($from, $untilExclusive);
reportsExpect((float) $inventory['summary']['inventory_value'] === 5000.0, 'Current inventory valuation is incorrect.');
reportsExpect(count($inventory['movements']) === 1, 'Inventory movement report did not apply date bounds.');

$events = $reports->events($from, $untilExclusive);
reportsExpect((int) $events['summary']['event_count'] === 1, 'Events report did not apply date bounds.');
reportsExpect((int) $events['summary']['reserved_guests'] === 3, 'Event reservation count is incorrect.');
reportsExpect((int) $events['summary']['checked_in_guests'] === 1, 'Event check-in count is incorrect.');

$customers = $reports->customers($from, $untilExclusive);
reportsExpect((int) $customers['summary']['customer_count'] === 1, 'Customer report did not apply date bounds.');
reportsExpect((int) $customers['memberships']['membership_count'] === 1, 'Active membership count is incorrect.');
reportsExpect((int) $customers['points']['points_earned'] === 100, 'Points earned should be filtered by period.');
reportsExpect((int) $customers['points']['points_redeemed'] === 25, 'Points redeemed total is incorrect.');
reportsExpect((int) $customers['rows'][0]['points_balance'] === 975, 'Customer balance should include lifetime points.');

$executive = $reports->executive($from, $untilExclusive);
reportsExpect((int) $executive['sales']['sale_count'] === 1, 'Executive sales KPI is incorrect.');
reportsExpect((int) $executive['customers']['customer_count'] === 1, 'Executive customer KPI is incorrect.');

$controller = new ReportController($reports);
$response = $controller->index(new Request('GET', '/reports', [
    'report' => 'sales',
    'from' => $from,
    'to' => '2026-11-07',
]));
reportsExpect($response->statusCode() === 200, 'Valid report range should render.');
$invalidRange = $controller->index(new Request('GET', '/reports', [
    'report' => 'sales',
    'from' => '2026-11-08',
    'to' => '2026-11-01',
]));
reportsExpect($invalidRange->statusCode() === 422, 'Invalid report ranges should be rejected.');
$invalidDate = $controller->index(new Request('GET', '/reports', [
    'report' => 'events',
    'from' => '2026-02-30',
    'to' => '2026-03-01',
]));
reportsExpect($invalidDate->statusCode() === 422, 'Invalid calendar dates should be rejected.');

$permissionMiddleware = new PermissionMiddleware(
    BLCKOS_CONFIG['route_permissions'],
    new RoleRepository($database)
);
$_SESSION['auth'] = ['id' => 1, 'email' => 'owner@example.test', 'auth_version' => 0];
reportsExpect($permissionMiddleware->process(
    new Request('GET', '/reports'),
    static fn (Request $request): Response => $controller->index($request)
)->statusCode() === 200, 'A user with reports.view should access reports.');
$_SESSION['auth'] = ['id' => 2, 'email' => 'employee@example.test', 'auth_version' => 0];
reportsExpect($permissionMiddleware->process(
    new Request('GET', '/reports'),
    static fn (Request $request): Response => $controller->index($request)
)->statusCode() === 403, 'A user without reports.view must not access reports.');

echo "Reports module tests passed." . PHP_EOL;
