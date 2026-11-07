<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $rep = new Reporte();
        $this->view('dashboard/index', [
            'titulo'    => 'Panel',
            'hoy'       => $rep->resumenHoy(),
            'top'       => $rep->topProductos(5),
            'stockBajo' => $rep->stockBajo(),
            'recientes' => array_slice((new Pedido())->listar(), 0, 5),
        ]);
    }
}
