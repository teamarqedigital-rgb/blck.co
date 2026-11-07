<?php
// Reglas de negocio de pedidos:
//  RN-01 No se vende un producto sin stock suficiente.
//  RN-02 Un pedido entregado o cancelado no se puede modificar.
//  RN-03 El total se calcula en el servidor desde el detalle (precio de la BD).
//  RN-04 No se entrega un pedido sin pago registrado.
class Pedido extends Model
{
    public const SIGUIENTE = ['pendiente' => 'preparando', 'preparando' => 'entregado'];

    public function listar(string $estado = ''): array
    {
        $sql = "SELECT p.id, p.fecha, p.estado, p.total, c.nombre AS cliente, u.nombre AS atendio,
                       (SELECT COUNT(*) FROM pago g WHERE g.pedido_id = p.id) AS pagado
                FROM pedido p
                JOIN cliente c ON c.id = p.cliente_id
                JOIN usuario u ON u.id = p.usuario_id";
        $params = [];
        if ($estado !== '') {
            $sql .= " WHERE p.estado = ?";
            $params[] = $estado;
        }
        return $this->query($sql . " ORDER BY p.fecha DESC", $params)->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $sql = "SELECT p.*, c.nombre AS cliente, u.nombre AS atendio
                FROM pedido p
                JOIN cliente c ON c.id = p.cliente_id
                JOIN usuario u ON u.id = p.usuario_id
                WHERE p.id = ?";
        return $this->query($sql, [$id])->fetch() ?: null;
    }

    public function detalle(int $id): array
    {
        $sql = "SELECT d.*, pr.nombre, d.cantidad * d.precio_unitario AS subtotal
                FROM detalle_pedido d JOIN producto pr ON pr.id = d.producto_id
                WHERE d.pedido_id = ?";
        return $this->query($sql, [$id])->fetchAll();
    }

    public function pago(int $id): ?array
    {
        return $this->query("SELECT * FROM pago WHERE pedido_id = ?", [$id])->fetch() ?: null;
    }

    /** Crea el pedido con transacción. Devuelve [id, error]. */
    public function crear(int $clienteId, int $usuarioId, array $items): array
    {
        $this->db->beginTransaction();
        try {
            $this->query("INSERT INTO pedido (cliente_id, usuario_id) VALUES (?,?)", [$clienteId, $usuarioId]);
            $pedidoId = (int)$this->db->lastInsertId();
            $total = 0;

            foreach ($items as $productoId => $cant) {
                $p = $this->query("SELECT nombre, precio, stock FROM producto
                                   WHERE id = ? AND activo = 1 FOR UPDATE", [$productoId])->fetch();
                if (!$p) {
                    throw new RuntimeException('Un producto del pedido ya no está disponible.');
                }
                if ($p['stock'] < $cant) {   // RN-01
                    throw new RuntimeException("Stock insuficiente de {$p['nombre']} (quedan {$p['stock']}).");
                }
                $this->query("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario)
                              VALUES (?,?,?,?)", [$pedidoId, $productoId, $cant, $p['precio']]);
                $this->query("UPDATE producto SET stock = stock - ? WHERE id = ?", [$cant, $productoId]);
                $total += $p['precio'] * $cant;   // RN-03
            }
            $this->query("UPDATE pedido SET total = ? WHERE id = ?", [$total, $pedidoId]);
            $this->db->commit();
            return [$pedidoId, null];
        } catch (RuntimeException $ex) {
            $this->db->rollBack();
            return [null, $ex->getMessage()];
        } catch (Throwable $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    /** Avanza el estado un paso. Devuelve mensaje de error o null. */
    public function avanzar(int $id): ?string
    {
        $p = $this->buscar($id);
        if (!$p || !isset(self::SIGUIENTE[$p['estado']])) {
            return 'Este pedido ya no se puede modificar.';          // RN-02
        }
        $nuevo = self::SIGUIENTE[$p['estado']];
        if ($nuevo === 'entregado' && !$this->pago($id)) {
            return 'Registra el pago antes de entregar el pedido.';  // RN-04
        }
        $this->query("UPDATE pedido SET estado = ? WHERE id = ?", [$nuevo, $id]);
        return null;
    }

    /** Cancela y devuelve el stock. */
    public function cancelar(int $id): ?string
    {
        $p = $this->buscar($id);
        if (!$p || !isset(self::SIGUIENTE[$p['estado']])) {
            return 'Este pedido ya no se puede modificar.';          // RN-02
        }
        if ($this->pago($id)) {
            return 'El pedido ya fue pagado; no se puede cancelar.';
        }
        $this->db->beginTransaction();
        foreach ($this->detalle($id) as $d) {
            $this->query("UPDATE producto SET stock = stock + ? WHERE id = ?", [$d['cantidad'], $d['producto_id']]);
        }
        $this->query("UPDATE pedido SET estado = 'cancelado' WHERE id = ?", [$id]);
        $this->db->commit();
        return null;
    }

    public function registrarPago(int $id, string $metodo): ?string
    {
        $p = $this->buscar($id);
        if (!$p || $p['estado'] === 'cancelado') {
            return 'No se puede registrar el pago de este pedido.';
        }
        if ($this->pago($id)) {
            return 'Este pedido ya tiene un pago registrado.';
        }
        if (!in_array($metodo, ['efectivo', 'tarjeta', 'transferencia'], true)) {
            return 'Método de pago no válido.';
        }
        $this->query("INSERT INTO pago (pedido_id, metodo, monto) VALUES (?,?,?)", [$id, $metodo, $p['total']]);
        return null;
    }
}
