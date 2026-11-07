<?php
class AuthController extends Controller
{
    public function login(): void
    {
        if (!empty($_SESSION['usuario'])) {
            $this->redirect('dashboard/index');
        }
        $error = null;
        if ($this->esPost()) {
            $correoEntrada = $_POST['correo'] ?? '';
            $claveEntrada = $_POST['clave'] ?? '';
            $correo = is_string($correoEntrada) ? mb_strtolower(trim($correoEntrada)) : '';
            $clave = is_string($claveEntrada) ? $claveEntrada : '';
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $clave === '') {
                $error = 'Escribe un correo válido y tu contraseña.';
            } else {
                $u = (new Usuario())->porCorreo($correo);
                if ($u && password_verify($clave, $u['clave'])) {
                    session_regenerate_id(true);
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    $_SESSION['usuario'] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'rol' => $u['rol']];
                    $this->redirect('dashboard/index');
                }
                $error = 'Correo o contraseña incorrectos.';
            }
        }
        $this->view('auth/login', ['error' => $error, 'titulo' => 'Ingresar'], 'auth');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Usa el formulario de cierre de sesión.');
        }
        csrf_check();
        $this->cerrarSesionActual();
        header('Location: ' . url('auth/login'));
        exit;
    }
}
