<?php
declare(strict_types=1);

use BlckOS\Controllers\EventController;
use BlckOS\Core\Request;
use BlckOS\Core\Response;
use BlckOS\Core\View;
use BlckOS\Middleware\CsrfMiddleware;
use BlckOS\Middleware\PermissionMiddleware;
use BlckOS\Models\EventException;
use BlckOS\Models\EventRepository;
use BlckOS\Models\RoleRepository;
use BlckOS\Models\AuditLogger;

require dirname(__DIR__) . '/bootstrap.php';

function eventsExpect(bool $condition, string $message): void
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
    'CREATE TABLE blck_os_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_type TEXT NOT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        venue TEXT NOT NULL,
        starts_at TEXT NOT NULL,
        ends_at TEXT NOT NULL,
        capacity INTEGER NOT NULL,
        status TEXT NOT NULL,
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE blck_os_event_reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL REFERENCES blck_os_events(id) ON DELETE RESTRICT,
        reservation_code TEXT UNIQUE,
        qr_token TEXT NOT NULL UNIQUE,
        guest_name TEXT NOT NULL,
        guest_email TEXT NOT NULL,
        guest_phone TEXT NOT NULL DEFAULT \'\',
        guest_count INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT \'CONFIRMED\',
        created_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        checked_in_by INTEGER NULL REFERENCES blck_os_users(id) ON DELETE SET NULL,
        checked_in_at TEXT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
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
$database->exec("INSERT INTO blck_os_roles (code, name, description) VALUES ('OWNER', 'Owner', ''), ('EMPLEADO', 'Employee', ''), ('CLIENTE', 'Client', '')");
$database->exec("INSERT INTO blck_os_permissions (permission_key, name, description) VALUES ('events.view', 'View events', ''), ('events.manage', 'Manage events', '')");
$database->exec(
    "INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id FROM blck_os_roles role_row CROSS JOIN blck_os_permissions permission_row
     WHERE role_row.code = 'OWNER'"
);
$database->exec(
    "INSERT INTO blck_os_role_permissions (role_id, permission_id)
     SELECT role_row.id, permission_row.id FROM blck_os_roles role_row
     JOIN blck_os_permissions permission_row ON permission_row.permission_key = 'events.view'
     WHERE role_row.code IN ('EMPLEADO', 'CLIENTE')"
);
$database->exec("INSERT INTO blck_os_users (email, role_id) VALUES ('owner@example.test', 1), ('employee@example.test', 2), ('client@example.test', 3)");

$events = new EventRepository($database, new DateTimeZone('America/Bogota'));
$roles = new RoleRepository($database);
$controller = new EventController($events, $roles, new AuditLogger($database));
$permissionMiddleware = new PermissionMiddleware(BLCKOS_CONFIG['route_permissions'], $roles);
$csrf = new CsrfMiddleware();
$_SESSION['csrf_token'] = str_repeat('c', 64);
$_SESSION['auth'] = ['id' => 1, 'email' => 'owner@example.test', 'auth_version' => 0];
$eventData = static function (
    string $type,
    string $title,
    string $status = 'PUBLISHED',
    int $capacity = 10
): array {
    return [
        'event_type' => $type,
        'title' => $title,
        'description' => 'Evento para pruebas <sin markup>',
        'venue' => 'BLCK Studio',
        'starts_at' => '2026-11-06T08:00',
        'ends_at' => '2026-11-06T11:00',
        'capacity' => $capacity,
        'status' => $status,
    ];
};

$eventIds = [];
foreach (EventRepository::TYPES as $type => $label) {
    $eventIds[$type] = $events->createEvent($eventData($type, $label), 1);
}
$morningEventId = $eventIds['MORNING_BEATS_COFFEE'];
$draftEventId = $events->createEvent($eventData('WORKSHOP', 'Taller privado', 'DRAFT'), 1);
eventsExpect(count($events->events(true)) === 5, 'All four event types and the draft event should be listed for staff.');
eventsExpect(count($events->events(false)) === 4, 'Clients should only see published event summaries.');
eventsExpect($events->eventTypeLabel('PRIVATE_EVENT') === 'Private Events', 'Private Events type label is missing.');
eventsExpect($events->formatDateTime('2026-11-06 13:00:00') === '06/11/2026 08:00', 'Event dates should be presented in the configured local timezone.');

foreach ([
    static fn () => $events->createEvent($eventData('UNKNOWN', 'Invalid type'), 1),
    static fn () => $events->createEvent(array_merge($eventData('DJ_SESSION', 'Invalid range'), ['ends_at' => '2026-11-06T07:00']), 1),
    static fn () => $events->createEvent(array_merge($eventData('DJ_SESSION', 'Invalid capacity'), ['capacity' => 0]), 1),
] as $invalidEvent) {
    try {
        $invalidEvent();
        eventsExpect(false, 'Invalid events must be rejected.');
    } catch (EventException) {
    }
}

$form = [
    'guest_name' => 'Ana Pérez',
    'guest_email' => 'ana@example.test',
    'guest_phone' => '+57 300 000 0000',
    'guest_count' => 2,
];
$reservation = $events->createReservation($morningEventId, $form, 1);
eventsExpect(preg_match('/^BLCK-E-\d{8}$/', $reservation['reservation_code']) === 1, 'Reservation code must use the internal ticket format.');
eventsExpect(preg_match('/^[a-f0-9]{64}$/', $reservation['qr_token']) === 1, 'Reservation QR should use a cryptographically random token.');
$storedReservation = $events->reservationById($reservation['id']);
eventsExpect($storedReservation['qr_token'] === $reservation['qr_token'], 'Reservation QR token must be available to authorized staff for reprinting.');
eventsExpect($storedReservation['guest_email'] === 'ana@example.test', 'Reservation contact details were not saved.');
eventsExpect((int) $events->event($morningEventId)['reserved_count'] === 2, 'Confirmed reservations must consume event capacity.');

try {
    $events->createReservation($morningEventId, array_merge($form, ['guest_count' => 9]), 1);
    eventsExpect(false, 'Reservations exceeding event capacity must be rejected.');
} catch (EventException $exception) {
    eventsExpect(str_contains($exception->getMessage(), 'cupos'), 'Capacity errors should be explained.');
}
eventsExpect(count($events->reservations($morningEventId)) === 1, 'A rejected reservation must not be stored.');

try {
    $events->createReservation($draftEventId, $form, 1);
    eventsExpect(false, 'Draft events must not accept reservations.');
} catch (EventException) {
}

try {
    $events->updateEvent($morningEventId, $eventData('MORNING_BEATS_COFFEE', 'Capacity conflict', 'PUBLISHED', 1));
    eventsExpect(false, 'Event capacity must not be reduced below confirmed reservations.');
} catch (EventException) {
}
try {
    $events->updateEvent($morningEventId, $eventData('MORNING_BEATS_COFFEE', 'Hidden event', 'DRAFT', 10));
    eventsExpect(false, 'A published event with reservations must not be hidden.');
} catch (EventException) {
}

$checkInResponse = $csrf->process(
    new Request('POST', '/events/check-in', [], ['qr_token' => $reservation['qr_token']]),
    static fn (): Response => Response::html('unexpected')
);
eventsExpect($checkInResponse->statusCode() === 403, 'Check-in must reject requests without CSRF protection.');
$validCheckIn = $csrf->process(
    new Request('POST', '/events/check-in', [], [
        'qr_token' => $reservation['qr_token'],
        'csrf_token' => str_repeat('c', 64),
    ]),
    static fn (Request $request): Response => $controller->checkIn($request)
);
eventsExpect($validCheckIn->statusCode() === 303, 'A valid reservation QR should redirect after check-in.');
$checkedIn = $events->reservationById($reservation['id']);
eventsExpect($checkedIn['status'] === 'CHECKED_IN' && $checkedIn['checked_in_at'] !== null, 'Check-in must atomically record attendance.');
try {
    $events->checkIn($reservation['qr_token'], 1);
    eventsExpect(false, 'A checked-in reservation QR must not be reusable.');
} catch (EventException $exception) {
    eventsExpect(str_contains($exception->getMessage(), 'ya registró'), 'Duplicate check-in should be explicitly rejected.');
}

$cancelledData = $eventData('DJ_SESSION', 'Sesión cancelada', 'CANCELLED', 10);
$cancelledEventId = $events->createEvent($cancelledData, 1);
try {
    $events->createReservation($cancelledEventId, $form, 1);
    eventsExpect(false, 'Cancelled events must not accept reservations.');
} catch (EventException) {
}

$_SESSION['auth'] = ['id' => 2, 'email' => 'employee@example.test', 'auth_version' => 0];
eventsExpect($permissionMiddleware->process(
    new Request('GET', '/events'),
    static fn (): Response => Response::html('allowed')
)->statusCode() === 200, 'A role with events.view should access the published event listing.');
eventsExpect($permissionMiddleware->process(
    new Request('GET', '/events/detail'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'A staff member without events.manage must not access reservations.');
eventsExpect($permissionMiddleware->process(
    new Request('POST', '/events/check-in'),
    static fn (): Response => Response::html('unexpected')
)->statusCode() === 403, 'A staff member without events.manage must not validate QR codes.');
$_SESSION['auth'] = ['id' => 1, 'email' => 'owner@example.test', 'auth_version' => 0];

eventsExpect($controller->index(new Request('GET', '/events'))->statusCode() === 200, 'Event management listing should render.');
eventsExpect($controller->newEvent(new Request('GET', '/events/new'))->statusCode() === 200, 'Event creation form should render.');
eventsExpect($controller->detail(new Request('GET', '/events/detail', ['id' => (string) $morningEventId]))->statusCode() === 200, 'Event details and reservations should render.');
eventsExpect($controller->reservation(new Request('GET', '/events/reservation', ['id' => (string) $reservation['id']]))->statusCode() === 200, 'Reservation ticket should render.');
eventsExpect($controller->reservation(new Request('GET', '/events/reservation', ['id' => '999']))->statusCode() === 404, 'Unknown reservation IDs must return 404.');
eventsExpect($controller->checkInPage(new Request('GET', '/events/check-in'))->statusCode() === 200, 'The QR check-in scanner should render.');
eventsExpect($controller->createReservation(new Request('POST', '/events/reservations', [], [
    'event_id' => 'invalid',
]))->statusCode() === 404, 'Invalid event reservation IDs must be rejected.');

$eventHtml = View::render('events/detail', [
    'event' => $events->event($morningEventId),
    'reservations' => $events->reservations($morningEventId),
    'canReserve' => false,
    'status' => '',
    'error' => null,
]);
eventsExpect(str_contains($eventHtml, 'Morning Beats &amp; Coffee'), 'Event names must be escaped in the event page.');
eventsExpect(str_contains($eventHtml, 'Ana Pérez'), 'Reservation details should appear only in the staff event page.');
$reservationHtml = View::render('events/reservation', [
    'reservation' => $events->reservationById($reservation['id']),
]);
eventsExpect(str_contains($reservationHtml, 'Ingreso registrado'), 'Checked-in reservation status should appear on the ticket.');
eventsExpect(!str_contains($reservationHtml, 'data-event-qr'), 'A redeemed reservation must not display a reusable QR.');
$checkInHtml = View::render('events/check-in', [
    'token' => '',
    'error' => null,
]);
eventsExpect(str_contains($checkInHtml, 'data-start-qr-scan'), 'Check-in view should expose the camera scanner.');
eventsExpect(str_contains($checkInHtml, 'name="csrf_token"'), 'Check-in form must include CSRF protection.');
$pendingReservation = $events->createReservation($morningEventId, array_merge($form, [
    'guest_name' => 'QR Guest',
    'guest_email' => 'qr@example.test',
    'guest_count' => 1,
]), 1);
$pendingTicket = View::render('events/reservation', [
    'reservation' => $events->reservationById($pendingReservation['id']),
]);
eventsExpect(str_contains($pendingTicket, 'data-event-qr'), 'A confirmed reservation should display its QR ticket.');
eventsExpect(str_contains($pendingTicket, 'qrcode-generator.js'), 'QR ticket must use the local QR encoder.');

fwrite(STDOUT, "Events module tests passed.\n");
