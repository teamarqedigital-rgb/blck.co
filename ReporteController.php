<?php
class ReporteController extends Controller
{
    public function index(): void
    {
        $this->requireRol('administrador');
        $r = new Reporte();
        $this->view('reportes/index', [
            'titulo'     => 'Reportes',
            'porMes'     => $r->ventasPorMes(),
            'top'        => $r->topProductos(10),
            'frecuentes' => $r->clientesFrecuentes(3),
            'metodos'    => $r->ventasPorMetodo(),
            'stockBajo'  => $r->stockBajo(),
        ]);
    }

    public function csv(): void
    {
        $this->requireRol('administrador');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ventas_por_mes.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");   // BOM para que Excel lea las tildes
        fputcsv($out, ['mes', 'pedidos', 'ingresos']);
        foreach ((new Reporte())->ventasPorMes() as $f) {
            fputcsv($out, [$f['mes'], $f['pedidos'], $f['ingresos']]);
        }
        fclose($out);
    }
}
