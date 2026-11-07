<?php
declare(strict_types=1);

use BlckOS\Controllers\InventoryController;
use BlckOS\Core\Request;
use BlckOS\Core\Response;
use BlckOS\Core\View;
use BlckOS\Middleware\CsrfMiddleware;
use BlckOS\Models\InventoryException;
use BlckOS\Models\InventoryRepository;
use BlckOS\Models\RoleRepository;
use BlckOS\Models\AuditLogger;

require dirname(__DIR__) . '/bootstrap.php';

function inventoryExpect(bool $condition, string $message): void
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
        role_id INTEGER NOT NULL REFERENCES blck_os_roles(id),
        permission_id INTEGER NOT NULL REFERENCES blck_os_permissions(id),
        PRIMARY KEY (role_id, permission_id)
    )'
);
$database->exec(
    'CREATE TABLE blck_os_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        is_active INTEGER NOT NULL DEFAULT 1,
        role_id INTEGER NOT NULL REFERENCES blck_os_roles(id)
    )'
);
$database->exec(
    'CREATE TABLE blck_os_inventory_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$database->exec(
    'CREATE TABLE blck_os_inventory_subcategories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL REFERENCES blck_os_inventory_categories(id),
        name TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (category_id, name)
    )'
);
$database->exec(
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
    )'
);
$database->exec(
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
$database->exec("INSERT INTO blck_os_roles (code, name, description) VALUES ('OWNER', 'Owner', 'Test owner')");
$database->exec("INSERT INTO blck_os_permissions (permission_key, name, description) VALUES ('inventory.view', 'View inventory', '')");
$database->exec("INSERT INTO blck_os_permissions (permission_key, name, description) VALUES ('inventory.manage', 'Manage inventory', '')");
$database->exec(
    'INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id FROM blck_os_roles role_row CROSS JOIN blck_os_permissions permission_row'
);
$database->exec(
    "INSERT INTO blck_os_users (email, role_id) VALUES ('owner@example.test', (SELECT id FROM blck_os_roles WHERE code = 'OWNER'))"
);
$userId = (int) $database->lastInsertId();
$_SESSION['auth'] = ['id' => $userId, 'email' => 'owner@example.test', 'auth_version' => 0];
$_SESSION['csrf_token'] = str_repeat('a', 64);

$inventory = new InventoryRepository($database);
$roles = new RoleRepository($database);
$controller = new InventoryController($inventory, $roles, new AuditLogger($database));

$inventory->createCategory('Bebidas');
$inventory->createCategory('Alimentos');
$categories = $inventory->categories();
$drinksId = (int) $categories[0]['id'];
$foodsId = (int) $categories[1]['id'];
try {
    $inventory->createCategory('Bebidas');
    inventoryExpect(false, 'Duplicate category names must be rejected.');
} catch (InventoryException $exception) {
    inventoryExpect(str_contains($exception->getMessage(), 'categoría'), 'Duplicate category error should be explicit.');
}

$inventory->createSubcategory($drinksId, 'Café');
$inventory->createSubcategory($drinksId, 'Té');
$subcategories = $inventory->subcategories();
$coffeeSubcategoryId = (int) $subcategories[0]['id'];
$inventory->createSubcategory($foodsId, 'Café');

$productId = $inventory->createProduct('COF-001', 'Café de origen', $drinksId, $coffeeSubcategoryId, '12500.50');
$product = $inventory->findProduct($productId);
inventoryExpect((int) $product['stock_quantity'] === 0, 'New products must start with zero stock.');
inventoryExpect($product['unit_price'] === '12500.5' || (float) $product['unit_price'] === 12500.5, 'The product unit price was not stored.');
try {
    $inventory->createProduct('COF-002', 'Categoría incorrecta', $foodsId, $coffeeSubcategoryId, '100.00');
    inventoryExpect(false, 'A subcategory from another category must not be assigned.');
} catch (InventoryException $exception) {
    inventoryExpect(str_contains($exception->getMessage(), 'no pertenece'), 'Subcategory/category mismatch should be explained.');
}
try {
    $inventory->createProduct('COF-001', 'SKU duplicado', $drinksId, null, '100.00');
    inventoryExpect(false, 'Duplicate product SKUs must be rejected.');
} catch (InventoryException $exception) {
    inventoryExpect(str_contains($exception->getMessage(), 'SKU'), 'Duplicate SKU error should be explicit.');
}

$inventory->recordMovement($productId, 'IN', 20, 'Recepción inicial', $userId);
$inventory->recordMovement($productId, 'OUT', 5, 'Consumo interno', $userId);
$inventory->recordMovement($productId, 'ADJUSTMENT', -2, 'Conteo físico', $userId);
$inventory->recordMovement($productId, 'ADJUSTMENT', 4, 'Corrección de conteo', $userId);
inventoryExpect((int) $inventory->findProduct($productId)['stock_quantity'] === 17, 'Stock should reflect all recorded movements.');
$movementRows = $database->query(
    'SELECT movement_type, quantity, stock_before, stock_after
     FROM blck_os_inventory_movements ORDER BY id'
)->fetchAll(PDO::FETCH_ASSOC);
inventoryExpect($movementRows[0]['movement_type'] === 'IN' && (int) $movementRows[0]['quantity'] === 20, 'Incoming stock movement was not recorded.');
inventoryExpect($movementRows[1]['movement_type'] === 'OUT' && (int) $movementRows[1]['quantity'] === -5, 'Outgoing stock must be stored as a negative ledger delta.');
inventoryExpect((int) $movementRows[3]['stock_before'] === 13 && (int) $movementRows[3]['stock_after'] === 17, 'Movement ledger stock snapshots are inconsistent.');

$movementCount = count($inventory->movements());
foreach ([
    static fn () => $inventory->recordMovement($productId, 'OUT', 18, 'Insufficient stock', $userId),
    static fn () => $inventory->recordMovement($productId, 'IN', -2, 'Invalid incoming movement', $userId),
    static fn () => $inventory->recordMovement($productId, 'ADJUSTMENT', 0, 'Zero adjustment', $userId),
    static fn () => $inventory->recordMovement($productId, 'IN', 2147483648, 'Quantity out of range', $userId),
] as $invalidMovement) {
    try {
        $invalidMovement();
        inventoryExpect(false, 'Invalid stock movements must be rejected.');
    } catch (InventoryException) {
        inventoryExpect((int) $inventory->findProduct($productId)['stock_quantity'] === 17, 'A rejected movement changed stock.');
        inventoryExpect(count($inventory->movements()) === $movementCount, 'A rejected movement was added to the ledger.');
    }
}

$inventory->updateProduct($productId, 'COF-NEW', 'Café actualizado', $drinksId, null, '13000.00');
inventoryExpect((int) $inventory->findProduct($productId)['stock_quantity'] === 17, 'Editing product details must preserve stock.');
inventoryExpect($inventory->products()[0]['sku'] === 'COF-NEW', 'Updated product details were not listed.');

$csrf = new CsrfMiddleware();
$invalidCsrf = $csrf->process(
    new Request('POST', '/inventory/categories', [], ['name' => 'No autorizado']),
    static fn (): Response => Response::html('unexpected')
);
inventoryExpect($invalidCsrf->statusCode() === 403, 'Inventory write requests must reject invalid CSRF tokens.');
$categoryResponse = $csrf->process(
    new Request('POST', '/inventory/categories', [], [
        'name' => 'Accesorios',
        'csrf_token' => $_SESSION['csrf_token'],
    ]),
    static fn (Request $request): Response => $controller->createCategory($request)
);
inventoryExpect($categoryResponse->statusCode() === 303, 'A valid CSRF-protected category form should redirect.');
inventoryExpect($controller->createCategory(new Request('POST', '/inventory/categories', [], [
    'name' => 'Bebidas',
]))->statusCode() === 422, 'The category form should display duplicate validation errors.');
inventoryExpect($controller->index(new Request('GET', '/inventory'))->statusCode() === 200, 'The inventory page should render.');

$page = View::render('inventory/index', [
    'categories' => $inventory->categories(),
    'subcategories' => $inventory->subcategories(),
    'products' => $inventory->products(),
    'movements' => $inventory->movements(),
    'canManageInventory' => true,
    'product' => null,
    'error' => null,
    'status' => '',
]);
foreach (['Inventario', 'Categorías', 'Subcategorías', 'Productos y existencias', 'Registrar movimiento', 'Movimientos recientes', 'Café actualizado'] as $label) {
    inventoryExpect(str_contains($page, $label), 'Inventory page is missing: ' . $label);
}

fwrite(STDOUT, "Inventory module tests passed.\n");
