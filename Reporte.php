<?php
class Reporte extends Model
{
    public function resumenHoy(): array
    {
        $r = $this->query("SELECT COUNT(*) AS pedidos, COALESCE(SUM(total),0) AS ingresos
                           FROM pedido WHERE DATE(fecha) = CURDATE() AND estado <> 'cancelado'")->fetch();
        $r['pendientes'] = (int)$this->query("SELECT COUNT(*) FROM pedido
                           WHERE estado IN ('pendiente','preparando')")->fetchColumn();
        return $r;
    }

    public function ventasPorMes(): array
    {
        return $this->query("SELECT DATE_FORMAT(fecha,'%Y-%m') AS mes, COUNT(*) AS pedidos, SUM(total) AS ingresos
                             FROM pedido WHERE estado = 'entregado'
                             GROUP BY mes ORDER BY mes")->fetchAll();
    }

    public function topProductos(int $limite = 5): array
    {
        $sql = "SELECT pr.nombre, SUM(d.cantidad) AS vendidos, SUM(d.cantidad * d.precio_unitario) AS ingresos
                FROM detalle_pedido d
                JOIN producto pr ON pr.id = d.producto_id
                JOIN pedido p ON p.id = d.pedido_id
                WHERE p.estado <> 'cancelado'
                GROUP BY pr.id ORDER BY vendidos DESC LIMIT " . (int)$limite;
        return $this->query($sql)->fetchAll();
    }

    public function stockBajo(): array
    {
        return $this->query("SELECT nombre, stock FROM producto
                             WHERE activo = 1 AND stock <= ? ORDER BY stock", [STOCK_BAJO])->fetchAll();
    }

    public function clientesFrecuentes(int $min = 3): array
    {
        return $this->query("SELECT c.nombre, COUNT(*) AS pedidos, SUM(p.total) AS gastado
                             FROM pedido p JOIN cliente c ON c.id = p.cliente_id
                             WHERE p.estado = 'entregado'
                             GROUP BY c.id HAVING COUNT(*) >= ? ORDER BY pedidos DESC", [$min])->fetchAll();
    }

    public function ventasPorMetodo(): array
    {
        return $this->query("SELECT metodo, COUNT(*) AS pagos, SUM(monto) AS monto
                             FROM pago GROUP BY metodo ORDER BY monto DESC")->fetchAll();
    }
}
