<?php
class ClienteController extends Controller
{
    private Cliente $m;

    public function __construct()
    {
        $this->m = new Cliente();
    }

    public function index(): void
    {
        $this->requireLogin();
        $q = trim($_GET['q'] ?? '');
        $this->view('clientes/index', ['titulo' => 'Clientes', 'q' => $q, 'clientes' => $this->m->listar($q)]);
    }

    public function crear(): void
    {
        $this->requireLogin();
        $this->formulario(null);
    }

    public function editar(): void
    {
        $this->requireLogin();
        $item = $this->m->buscar((int)($_GET['id'] ?? 0));
        if (!$item) {
            $this->redirect('cliente/index');
        }
        $this->formulario($item);
    }

    private function formulario(?array $item): void
    {
        $errores = [];
        if ($this->esPost()) {
            $d = [
                'nombre'   => trim($_POST['nombre'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'correo'   => trim($_POST['correo'] ?? ''),
            ];
            if (mb_strlen($d['nombre']) < 3 || mb_strlen($d['nombre']) > 80) {
                $errores['nombre'] = 'El nombre debe tener entre 3 y 80 caracteres.';
            }
            if ($d['telefono'] !== '' && !preg_match('/^[0-9]{7,15}$/', $d['telefono'])) {
                $errores['telefono'] = 'El teléfono debe tener solo números (7 a 15 dígitos).';
            }
            if ($d['correo'] !== '' && !filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'El correo no tiene un formato válido.';
            }
            if (!$errores) {
                $item ? $this->m->actualizar($item['id'], $d) : $this->m->crear($d);
                flash('ok', 'Cliente guardado.');
                $this->redirect('cliente/index');
            }
            $item = $d + ['id' => $item['id'] ?? 0];
        }
        $this->view('clientes/form', ['titulo' => $item ? 'Editar cliente' : 'Nuevo cliente',
                                      'item' => $item, 'errores' => $errores]);
    }

    public function eliminar(): void
    {
        $this->requireRol('administrador');
        if ($this->esPost()) {
            $this->m->eliminar((int)($_POST['id'] ?? 0))
                ? flash('ok', 'Cliente eliminado.')
                : flash('error', 'No se puede eliminar: el cliente tiene pedidos.');
        }
        $this->redirect('cliente/index');
    }
}
