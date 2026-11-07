<?php
class Cliente extends Model
{
    public function listar(string $q = ''): array
    {
        $sql = "SELECT c.*, COUNT(p.id) AS pedidos
                FROM cliente c LEFT JOIN pedido p ON p.cliente_id = c.id
                WHERE c.nombre LIKE ?
                GROUP BY c.id ORDER BY c.nombre";
        return $this->query($sql, ["%$q%"])->fetchAll();
    }

    public function todos(): array
    {
        return $this->query("SELECT id, nombre FROM cliente ORDER BY nombre")->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        return $this->query("SELECT * FROM cliente WHERE id = ?", [$id])->fetch() ?: null;
    }

    public function crear(array $d): void
    {
        $this->query("INSERT INTO cliente (nombre, telefono, correo) VALUES (?,?,?)",
            [$d['nombre'], $d['telefono'] ?: null, $d['correo'] ?: null]);
    }

    public function actualizar(int $id, array $d): void
    {
        $this->query("UPDATE cliente SET nombre=?, telefono=?, correo=? WHERE id=?",
            [$d['nombre'], $d['telefono'] ?: null, $d['correo'] ?: null, $id]);
    }

    public function eliminar(int $id): bool
    {
        $n = $this->query("SELECT COUNT(*) FROM pedido WHERE cliente_id = ?", [$id])->fetchColumn();
        if ($n > 0) {
            return false;
        }
        $this->query("DELETE FROM cliente WHERE id = ?", [$id]);
        return true;
    }
}
