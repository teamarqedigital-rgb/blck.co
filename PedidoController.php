<?php
class PedidoController extends Controller
{
    private Pedido $m;

    public function __construct()
    {
        $this->m = new Pedido();
    }

    public function index(): void
    {
        $this->requireLogin();
        $estado = $_GET['estado'] ?? '';
        if (!in_array($estado, ['pendiente', 'preparando', 'entregado', 'cancelado'], true)) {
            $estado = '';
        }
        $this->view('pedidos/index', ['titulo' => 'Pedidos', 'estado' => $estado,
                                      'pedidos' => $this->m->listar($estado)]);
    }

    public function crear(): void
    {
        $this->requireLogin();
        $error = null;
        if ($this->esPost()) {
            $clienteId = (int)($_POST['cliente_id'] ?? 0);
            $items = [];
            foreach (($_POST['cantidad'] ?? []) as $pid => $cant) {
                $cant = (int)$cant;
                if ($cant > 0) {
                    $items[(int)$pid] = $cant;
                }
            }
            if (!(new Cliente())->buscar($clienteId)) {
                $error = 'Elige un cliente.';
            } elseif (!$items) {
                $error = 'Agrega al menos un producto al pedido.';
            } else {
                [$id, $error] = $this->m->crear($clienteId, $_SESSION['usuario']['id'], $items);
                if ($id) {
                    flash('ok', "Pedido #$id registrado.");
                    $this->redirect('pedido/ver', ['id' => $id]);
                }
            }
        }
        $this->view('pedidos/crear', [
            'titulo'    => 'Nuevo pedido',
            'scripts'   => ['js/pedido.js'],
            'error'     => $error,
            'clientes'  => (new Cliente())->todos(),
            'productos' => (new Producto())->disponibles(),
        ]);
    }

    public function ver(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $p = $this->m->buscar($id);
        if (!$p) {
            $this->redirect('pedido/index');
        }
        $this->view('pedidos/ver', ['titulo' => "Pedido #$id", 'p' => $p,
                                    'detalle' => $this->m->detalle($id), 'pago' => $this->m->pago($id)]);
    }

    public function avanzar(): void
    {
        $this->accion(fn($id) => $this->m->avanzar($id), 'Estado actualizado.');
    }

    public function cancelar(): void
    {
        $this->accion(fn($id) => $this->m->cancelar($id), 'Pedido cancelado y stock devuelto.');
    }

    public function pagar(): void
    {
        $this->accion(fn($id) => $this->m->registrarPago($id, $_POST['metodo'] ?? ''), 'Pago registrado.');
    }

    private function accion(callable $fn, string $okMsg): void
    {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        if ($this->esPost()) {
            $err = $fn($id);
            $err ? flash('error', $err) : flash('ok', $okMsg);
        }
        $this->redirect('pedido/ver', ['id' => $id]);
    }
}
