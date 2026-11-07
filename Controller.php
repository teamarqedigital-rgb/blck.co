<?php
// Clase base de todos los controladores.
abstract class Controller
{
    protected function view(string $vista, array $datos = [], string $layout = 'main'): void
    {
        extract($datos);
        $vistas = [
            'auth/login'       => 'login.php',
            'menu/index'       => 'index (6).php',
            'dashboard/index'  => 'index (9).php',
            'categorias/index' => 'index (1).php',
            'productos/index'  => 'index (2).php',
            'pedidos/index'    => 'index (5).php',
            'reportes/index'   => 'index (4).php',
            'clientes/index'   => 'index (7).php',
            'categorias/form'  => 'form.php',
            'productos/form'   => 'form (3).php',
            'clientes/form'    => 'form (8).php',
            'pedidos/crear'    => 'crear.php',
            'pedidos/ver'      => 'ver.php',
            'usuarios/index'   => 'usuarios.php',
            'usuarios/form'    => 'usuario_form.php',
        ];
        if (!isset($vistas[$vista])) {
            throw new RuntimeException("Vista no configurada: {$vista}");
        }
        $archivo = __DIR__ . '/' . $vistas[$vista];
        require __DIR__ . "/{$layout}.php";
        unset($_SESSION['old']);
    }

    protected function redirect(string $ruta, array $params = []): void
    {
        header('Location: ' . url($ruta, $params));
        exit;
    }

    protected function requireLogin(): void
    {
        $id = (int)($_SESSION['usuario']['id'] ?? 0);
        $usuario = $id > 0 ? (new Usuario())->paraSesion($id) : null;
        if (!$usuario) {
            $this->cerrarSesionActual();
            $this->redirect('auth/login');
        }
        $_SESSION['usuario'] = $usuario;
    }

    protected function requireRol(string ...$roles): void
    {
        $this->requireLogin();
        if (!in_array($_SESSION['usuario']['rol'], $roles, true)) {
            flash('error', 'No tienes permiso para esa sección.');
            $this->redirect('dashboard/index');
        }
    }

    protected function esPost(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            return true;
        }
        return false;
    }

    protected function guardarOld(array $datos): void
    {
        $_SESSION['old'] = $datos;
    }

    protected function cerrarSesionActual(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}
