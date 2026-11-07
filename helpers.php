<?php
// Funciones de ayuda usadas en controladores y vistas.

function e($v): string            // escapa HTML (previene XSS)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url(string $ruta = '', array $params = []): string
{
    $q = $ruta !== '' ? ['r' => $ruta] + $params : $params;
    return BASE_URL . '/index.php' . ($q ? '?' . http_build_query($q) : '');
}

function asset(string $path): string
{
    $nombre = basename($path);
    $publico = __DIR__ . '/public/' . ltrim($path, '/');
    return BASE_URL . (is_file($publico) ? '/public/' . ltrim($path, '/') : '/' . $nombre);
}

function money($n): string
{
    return '$' . number_format((float)$n, 0, ',', '.');
}

function flash(?string $tipo = null, ?string $msg = null)
{
    if ($tipo !== null) {                       // guardar
        $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;            // leer una sola vez
    unset($_SESSION['flash']);
    return $f;
}

function old(string $campo, $defecto = '')
{
    return $_SESSION['old'][$campo] ?? $defecto;
}

// Protección CSRF
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    $t = $_POST['csrf'] ?? '';
    $esperado = $_SESSION['csrf'] ?? '';
    if (!is_string($t) || !is_string($esperado) || $esperado === '' || !hash_equals($esperado, $t)) {
        http_response_code(403);
        exit('Sesión expirada. Recarga la página e inténtalo de nuevo.');
    }
}
