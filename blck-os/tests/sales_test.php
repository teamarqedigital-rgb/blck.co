<?php
declare(strict_types=1);

use BlckOS\Controllers\SalesController;
use BlckOS\Core\Request;
use BlckOS\Core\Response;
use BlckOS\Core\View;
use BlckOS\Middleware\CsrfMiddleware;
use BlckOS\Middleware\PermissionMiddleware;
use BlckOS\Models\InventoryException;
use BlckOS\Models\RoleRepository;
use BlckOS\Models\SalesException;
use BlckOS\Models\SalesRepository;
use BlckOS\Models\AuditLogger;

require dirname(__DIR__) . '/bootstrap.php';

function salesExpect(bool $condition, string $message): void
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
    'CREATE TABLE blck_os_inventory_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_inventory_subcategories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL REFERENCES blck_os_inventory_categories(id),
        name TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (category_id, name)
    )',
    'CREATE TABLE blck_os_inventory_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sku TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        category_id INTEGER NOT NULL REFERENCES blck_os_inventory_categories(id),
        subcategory_id INTEGER NULL REFERENCES blck_os_inventory_subcategories(id),
        unit_price NUMERIC NOT NULL DEFAULT 0,
        stock_quantity INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_inventory_movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL REFERENCES blck_os_inventory_products(id),
        movement_type TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        stock_before INTEGER NOT NULL,
        stock_after INTEGER NOT NULL,
        note TEXT NOT NULL,
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_sales (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ticket_number TEXT UNIQUE,
        submission_key TEXT NOT NULL UNIQUE,
        subtotal NUMERIC NOT NULL,
        total NUMERIC NOT NULL,
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_sale_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sale_id INTEGER NOT NULL REFERENCES blck_os_sales(id) ON DELETE RESTRICT,
        product_id INTEGER NOT NULL REFERENCES blck_os_inventory_products(id) ON DELETE RESTRICT,
        sku TEXT NOT NULL,
        product_name TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        unit_price NUMERIC NOT NULL,
        line_total NUMERIC NOT NULL,
        UNIQUE (sale_id, product_id)
    )',
    'CREATE TABLE blck_os_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sale_id INTEGER NOT NULL REFERENCES blck_os_sales(id) ON DELETE RESTRICT,
        payment_method TEXT NOT NULL,
        amount NUMERIC NOT NULL,
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
] as $statement) {
    $database->exec($statement);
}
$database->exec('ALTER TABLE blck_os_inventory_movements ADD COLUMN sale_id INTEGER NULL REFERENCES blck_os_sales(id)');
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
$database->exec("INSERT INTO blck_os_roles (code, name, description) VALUES ('OWNER', 'Owner', 'Test owner'), ('EMPLEADO', 'Employee', 'Test employee')");
$database->exec("INSERT INTO blck_os_permissions (permission_key, name, description) VALUES ('sales.view', 'View sales', ''), ('sales.manage', 'Manage sales', '')");
$database->exec(
    "INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id FROM blck_os_roles role_row CROSS JOIN blck_os_permissions permission_row
     WHERE role_row.code = 'OWNER'"
);
$database->exec(
    "INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id FROM blck_os_roles role_row
     JOIN blck_os_permissions permission_row ON permission_row.permission_key = 'sales.view'
     WHERE role_row.code = 'EMPLEADO'"
);
$database->exec("INSERT INTO blck_os_users (email, role_id) VALUES ('owner@example.test', 1), ('employee@example.test', 2)");
$database->exec("INSERT INTO blck_os_inventory_categories (name) VALUES ('Café')");
$database->exec(
    "INSERT INTO blck_os_inventory_products (sku, name, category_id, unit_price, stock_quantity)
     VALUES ('COF-001', 'Café <Origen>', 1, '12.50', 10), ('COF-002', 'Filtro', 1, '7.25', 10)"
);
$userId = 1;
$_SESSION['auth'] = ['id' => $userId, 'email' => 'owner@example.test', 'auth_version' => 0];
$_SESSION['csrf_token'] = str_repeat('b', 64);

$sales = new SalesRepository($database);
$roles = new RoleRepository($database);
$controller = new SalesController($sales, $roles, new AuditLogger($database));
$csrf = new CsrfMiddleware();
$permissionMiddleware = new PermissionMiddleware(BLCKOS_CONFIG['route_permissions'], $roles);
$productStock = static function (int $productId) use ($database): int {
    $statement = $database->prepare('SELECT stock_quantity FROM blck_os_inventory_products WHERE id = :id');
    $statement->execute(['id' => $productId]);
    return (int) $statement->fetchColumn();
};

$employeeSession = $_SESSION['auth'];
$_SESSION['auth'] = ['id' => 2, 'email' => 'employee@example.test', 'auth_version' => 0];
salesExpect($permissionMiddleware->process(
    new Request('GET', '/sales'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'A permitted employee should see sales.');
salesExpect($permissionMiddleware->process(
    new Request('GET', '/sales/new'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'An employee without sales.manage must not create sales.');
$_SESSION['auth'] = $employeeSession;

$blockedCreate = $csrf->process(
    new Request('POST', '/sales/create', [], ['payment_method' => 'CASH']),
    static fn (): Response => Response::html('unexpected')
);
salesExpect($blockedCreate->statusCode() === 403, 'Sale creation must reject invalid CSRF tokens.');

$createResponse = $csrf->process(
    new Request('POST', '/sales/create', [], [
        'csrf_token' => $_SESSION['csrf_token'],
        'product_ids' => ['1', '2', '1'],
        'quantities' => ['2', '2', '1'],
        'payment_method' => 'TRANSFER',
        'submission_key' => str_repeat('c', 64),
    ]),
    static fn (Request $request): Response => $controller->create($request)
);
salesExpect($createResponse->statusCode() === 303, 'A valid sale should be created and redirected to its ticket.');
$saleRows = $sales->sales();
salesExpect(count($saleRows) === 1, 'The sale should be visible in the history.');
$saleId = (int) $saleRows[0]['id'];
$receipt = $sales->receipt($saleId);
salesExpect(is_array($receipt), 'The created sale should have a printable receipt.');
salesExpect($receipt['sale']['total'] === '52.00' || (float) $receipt['sale']['total'] === 52.0, 'Sale total must be calculated from stored product prices.');
salesExpect(str_starts_with($receipt['sale']['ticket_number'], 'BLCK-'), 'The internal ticket number should be generated by the server.');
salesExpect(count($receipt['items']) === 2, 'Duplicate product rows should be merged into one sale line.');
salesExpect((int) $receipt['items'][0]['quantity'] === 3, 'Duplicate product quantities should be aggregated.');
salesExpect($receipt['payments'][0]['payment_method'] === 'TRANSFER', 'The selected payment method should be recorded.');
salesExpect((float) $receipt['payments'][0]['amount'] === 52.0, 'The payment amount must equal the server-calculated total.');
salesExpect($productStock(1) === 7 && $productStock(2) === 8, 'Sale quantities must be deducted from inventory.');
$movement = $database->query('SELECT * FROM blck_os_inventory_movements WHERE sale_id = ' . $saleId . ' AND product_id = 1')->fetch(PDO::FETCH_ASSOC);
salesExpect(is_array($movement) && (int) $movement['quantity'] === -3, 'Sale stock deductions must be linked to inventory movements.');
salesExpect((int) $movement['stock_before'] === 10 && (int) $movement['stock_after'] === 7, 'Sale movement balances must match product stock.');
$replayedSaleId = $sales->createSale([1 => 3, 2 => 2], 'TRANSFER', $userId, str_repeat('c', 64));
salesExpect($replayedSaleId === $saleId, 'Replaying a POS submission must return its original sale.');
salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_sales')->fetchColumn() === 1, 'Replaying a POS submission must not duplicate the sale.');

$countsBeforeInvalid = [
    'sales' => (int) $database->query('SELECT COUNT(*) FROM blck_os_sales')->fetchColumn(),
    'items' => (int) $database->query('SELECT COUNT(*) FROM blck_os_sale_items')->fetchColumn(),
    'payments' => (int) $database->query('SELECT COUNT(*) FROM blck_os_payments')->fetchColumn(),
    'movements' => (int) $database->query('SELECT COUNT(*) FROM blck_os_inventory_movements')->fetchColumn(),
];
foreach ([
    static fn () => $sales->createSale([1 => 8], 'CASH', $userId, str_repeat('d', 64)),
    static fn () => $sales->createSale([999 => 1], 'CASH', $userId, str_repeat('e', 64)),
    static fn () => $sales->createSale([1 => 1], 'INVALID', $userId, str_repeat('f', 64)),
    static fn () => $sales->createSale([1 => 2147483648], 'CASH', $userId, str_repeat('a', 64)),
] as $invalidSale) {
    try {
        $invalidSale();
        salesExpect(false, 'Invalid or out-of-stock sales must be rejected.');
    } catch (InventoryException|SalesException) {
        salesExpect($productStock(1) === 7, 'A rejected sale changed stock.');
        salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_sales')->fetchColumn() === $countsBeforeInvalid['sales'], 'A rejected sale left a sales record.');
        salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_sale_items')->fetchColumn() === $countsBeforeInvalid['items'], 'A rejected sale left line items.');
        salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_payments')->fetchColumn() === $countsBeforeInvalid['payments'], 'A rejected sale left a payment.');
        salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_inventory_movements')->fetchColumn() === $countsBeforeInvalid['movements'], 'A rejected sale left stock movements.');
    }
}

$database->exec(
    "CREATE TRIGGER reject_card_payment BEFORE INSERT ON blck_os_payments
     WHEN NEW.payment_method = 'CARD'
     BEGIN SELECT RAISE(ABORT, 'Test payment failure'); END"
);
try {
    $sales->createSale([1 => 1], 'CARD', $userId, str_repeat('7', 64));
    salesExpect(false, 'A payment database failure must be surfaced.');
} catch (PDOException $exception) {
    salesExpect(str_contains($exception->getMessage(), 'Test payment failure'), 'Unexpected payment failure was swallowed.');
    salesExpect($productStock(1) === 7, 'A failed payment must roll back the inventory change.');
    salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_sales')->fetchColumn() === $countsBeforeInvalid['sales'], 'A failed payment must roll back the sales record.');
    salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_sale_items')->fetchColumn() === $countsBeforeInvalid['items'], 'A failed payment must roll back sale lines.');
    salesExpect((int) $database->query('SELECT COUNT(*) FROM blck_os_inventory_movements')->fetchColumn() === $countsBeforeInvalid['movements'], 'A failed payment must roll back stock movements.');
}
$database->exec('DROP TRIGGER reject_card_payment');

salesExpect($controller->create(new Request('POST', '/sales/create', [], [
    'product_ids' => ['1'],
    'quantities' => ['0'],
    'payment_method' => 'CASH',
    'submission_key' => str_repeat('b', 64),
]))->statusCode() === 422, 'Invalid sales form data should be rejected.');
salesExpect($controller->newSale(new Request('GET', '/sales/new'))->statusCode() === 200, 'The POS form should render for an authorized user.');
salesExpect($controller->index(new Request('GET', '/sales'))->statusCode() === 200, 'The sales history should render.');
salesExpect($controller->ticket(new Request('GET', '/sales/ticket', ['id' => (string) $saleId]))->statusCode() === 200, 'The printable ticket should render.');
salesExpect($controller->ticket(new Request('GET', '/sales/ticket', ['id' => '999']))->statusCode() === 404, 'Unknown ticket IDs should return 404.');

$newSaleHtml = View::render('sales/new', [
    'title' => 'Nueva venta',
    'products' => $sales->products(),
    'error' => null,
    'submissionKey' => str_repeat('b', 64),
]);
salesExpect(str_contains($newSaleHtml, 'name="submission_key"'), 'POS should include its one-time submission key.');
salesExpect(str_contains($newSaleHtml, 'data-add-sale-row'), 'POS should let the cashier add sale lines.');
salesExpect(str_contains($newSaleHtml, 'Café &lt;Origen&gt;'), 'Product text must be escaped in the POS.');

$receiptHtml = View::render('sales/ticket', [
    'title' => 'Comprobante interno',
    'receipt' => $receipt,
]);
salesExpect(str_contains($receiptHtml, 'Comprobante interno'), 'Ticket must identify itself as an internal receipt.');
salesExpect(str_contains($receiptHtml, 'NO ES FACTURA ELECTRÓNICA DIAN'), 'Ticket must not claim DIAN electronic invoice status.');
salesExpect(str_contains($receiptHtml, '&lt;Origen&gt;'), 'Product text must be escaped in the ticket.');
salesExpect(str_contains($receiptHtml, 'data-print-ticket'), 'Ticket should expose its print action.');

fwrite(STDOUT, "Sales module tests passed.\n");
