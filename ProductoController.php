<?php
class ProductoController extends Controller
{
    private Producto $m;

    public function __construct()
    {
        $this->m = new Producto();
    }

    public function index(): void
    {
        $this->requireLogin();
        $q = trim($_GET['q'] ?? '');
        $this->view('productos/index', ['titulo' => 'Productos', 'q' => $q, 'productos' => $this->m->listar($q)]);
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
            $this->redirect('producto/index');
        }
        $this->formulario($item);
    }

    private function formulario(?array $item): void
    {
        $errores = [];
        $cats = (new Categoria())->todas();
        if ($this->esPost()) {
            $d = [
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'descripcion'  => trim($_POST['descripcion'] ?? ''),
                'precio'       => $_POST['precio'] ?? '',
                'stock'        => $_POST['stock'] ?? '',
                'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
                'activo'       => isset($_POST['activo']) ? 1 : 0,
            ];
            if (mb_strlen($d['nombre']) < 3 || mb_strlen($d['nombre']) > 80) {
                $errores['nombre'] = 'El nombre debe tener entre 3 y 80 caracteres.';
            }
            if (!is_numeric($d['precio']) || $d['precio'] <= 0) {
                $errores['precio'] = 'El precio debe ser mayor que 0.';
            }
            if (filter_var($d['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
                $errores['stock'] = 'El stock debe ser un entero de 0 o más.';
            }
            if (!(new Categoria())->buscar($d['categoria_id'])) {
                $errores['categoria_id'] = 'Elige una categoría.';
            }
            if (!$errores) {
                $item ? $this->m->actualizar($item['id'], $d) : $this->m->crear($d);
                flash('ok', 'Producto guardado.');
                $this->redirect('producto/index');
            }
            $item = $d + ['id' => $item['id'] ?? 0];
        }
        $this->view('productos/form', ['titulo' => $item ? 'Editar producto' : 'Nuevo producto',
                                       'item' => $item, 'errores' => $errores, 'cats' => $cats]);
    }

    public function eliminar(): void
    {
        $this->requireRol('administrador');
        if ($this->esPost()) {
            $this->m->eliminar((int)($_POST['id'] ?? 0))
                ? flash('ok', 'Producto eliminado.')
                : flash('error', 'El producto tiene ventas registradas: quedó desactivado en lugar de borrarse.');
        }
        $this->redirect('producto/index');
    }
}
