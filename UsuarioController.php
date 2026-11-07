<?php
class UsuarioController extends Controller
{
    private Usuario $m;

    public function __construct()
    {
        $this->m = new Usuario();
    }

    public function index(): void
    {
        $this->requireRol('administrador');
        $this->view('usuarios/index', [
            'titulo' => 'Usuarios',
            'usuarios' => $this->m->listarGestionables(),
        ]);
    }

    public function crear(): void
    {
        $this->requireRol('administrador');
        $this->formulario(null);
    }

    public function editar(): void
    {
        $this->requireRol('administrador');
        $item = $this->m->buscarGestionable((int)($_GET['id'] ?? 0));
        if (!$item) {
            flash('error', 'No se encontró la cuenta solicitada.');
            $this->redirect('usuario/index');
        }
        $this->formulario($item);
    }

    private function formulario(?array $item): void
    {
        $errores = [];
        $roles = $this->m->rolesDisponibles();
        $esCuentaActual = $item && (int)$item['id'] === (int)$_SESSION['usuario']['id'];

        if ($this->esPost()) {
            $nombreEntrada = $_POST['nombre'] ?? '';
            $correoEntrada = $_POST['correo'] ?? '';
            $rolEntrada = $_POST['rol_id'] ?? '';
            $claveEntrada = $_POST['clave'] ?? '';
            $confirmacionEntrada = $_POST['confirmar_clave'] ?? '';
            $datos = [
                'nombre' => is_string($nombreEntrada) ? trim($nombreEntrada) : '',
                'correo' => is_string($correoEntrada) ? mb_strtolower(trim($correoEntrada)) : '',
                'rol_id' => filter_var($rolEntrada, FILTER_VALIDATE_INT) ?: 0,
                'activo' => isset($_POST['activo']) && $_POST['activo'] === '1' ? 1 : 0,
            ];
            $clave = is_string($claveEntrada) ? $claveEntrada : '';
            $confirmacion = is_string($confirmacionEntrada) ? $confirmacionEntrada : '';

            if ($esCuentaActual) {
                $datos['rol_id'] = (int)$item['rol_id'];
                $datos['activo'] = (int)$item['activo'];
            }

            if (mb_strlen($datos['nombre']) < 3 || mb_strlen($datos['nombre']) > 80) {
                $errores['nombre'] = 'El nombre debe tener entre 3 y 80 caracteres.';
            }
            if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || strlen($datos['correo']) > 100) {
                $errores['correo'] = 'Escribe un correo válido de máximo 100 caracteres.';
            } elseif ($this->m->correoExiste($datos['correo'], (int)($item['id'] ?? 0))) {
                $errores['correo'] = 'Ya existe una cuenta con ese correo.';
            }

            $idsRoles = array_map(static fn(array $rol): int => (int)$rol['id'], $roles);
            if (!in_array($datos['rol_id'], $idsRoles, true)) {
                $errores['rol_id'] = 'Elige uno de los roles disponibles.';
            }
            if (!$item && $clave === '') {
                $errores['clave'] = 'La contraseña es obligatoria para una cuenta nueva.';
            } elseif ($clave !== '' && (strlen($clave) < 12 || strlen($clave) > 72)) {
                $errores['clave'] = 'La contraseña debe tener entre 12 y 72 bytes.';
            }
            if ($clave !== '' && $clave !== $confirmacion) {
                $errores['confirmar_clave'] = 'Las contraseñas no coinciden.';
            }

            if (!$errores) {
                try {
                    if ($item) {
                        $this->m->actualizarGestionable(
                            (int)$item['id'],
                            $datos,
                            $clave !== '' ? $clave : null
                        );
                        if ($esCuentaActual && $clave !== '') {
                            session_regenerate_id(true);
                            $_SESSION['csrf'] = bin2hex(random_bytes(32));
                        }
                        flash('ok', 'Cuenta actualizada.');
                    } else {
                        $datos['clave'] = $clave;
                        $this->m->crearGestionable($datos);
                        flash('ok', 'Cuenta creada.');
                    }
                    $this->redirect('usuario/index');
                } catch (DomainException $e) {
                    $errores['general'] = $e->getMessage();
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }
                    $errores['correo'] = 'Ya existe una cuenta con ese correo.';
                }
            }
            $item = array_merge($item ?? [], $datos);
        }

        $this->view('usuarios/form', [
            'titulo' => $item ? 'Editar cuenta' : 'Nueva cuenta',
            'item' => $item,
            'roles' => $roles,
            'errores' => $errores,
            'esCuentaActual' => $esCuentaActual,
        ]);
    }
}
