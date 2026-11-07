<?php
// Menú público: lo ve cualquier persona sin iniciar sesión.
class MenuController extends Controller
{
    public function index(): void
    {
        $porCategoria = [];
        foreach ((new Producto())->menuPublico() as $p) {
            $porCategoria[$p['categoria']][] = $p;
        }
        $this->view('menu/index', ['menu' => $porCategoria, 'titulo' => 'Menú'], 'publico');
    }
}
