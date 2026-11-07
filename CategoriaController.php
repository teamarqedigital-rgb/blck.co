<?php
class CategoriaController extends Controller
{
    private Categoria $m;

    public function __construct()
    {
        $this->m = new Categoria();
    }

    public function index(): void
    {
        $this->requireRol('administrador');
        $this->view('categorias/index', ['titulo' => 'Categorías', 'categorias' => $this->m->todas()]);
    }

    public function crear(): void
    {
        $this->requireRol('administrador');
        $this->formulario(null);
    }

    public function editar(): void
    {
        $this->requireRol('administrador');
        $item = $this->m->buscar((int)($_GET['id'] ?? 0));
        if (!$item) {
            $this->redirect('categoria/index');
        }
        $this->formulario($item);
    }

    private function formulario(?array $item): void
    {
        $errores = [];
        if ($this->esPost()) {
            $nombre = trim($_POST['nombre'] ?? '');
            $id = $item['id'] ?? 0;
            if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 50) {
                $errores['nombre'] = 'El nombre debe tener entre 3 y 50 caracteres.';
            } elseif ($this->m->existeNombre($nombre, $id)) {
                $errores['nombre'] = 'Ya existe una categoría con ese nombre.';
            }
            if (!$errores) {
                $item ? $this->m->actualizar($id, $nombre) : $this->m->crear($nombre);
                flash('ok', 'Categoría guardada.');
                $this->redirect('categoria/index');
            }
            $item = ['id' => $id, 'nombre' => $nombre];
        }
        $this->view('categorias/form', ['titulo' => $item ? 'Editar categoría' : 'Nueva categoría',
                                        'item' => $item, 'errores' => $errores]);
    }

    public function eliminar(): void
    {
        $this->requireRol('administrador');
        if ($this->esPost()) {
            $ok = $this->m->eliminar((int)($_POST['id'] ?? 0));
            $ok ? flash('ok', 'Categoría eliminada.')
                : flash('error', 'No se puede eliminar: tiene productos asociados.');
        }
        $this->redirect('categoria/index');
    }
}
