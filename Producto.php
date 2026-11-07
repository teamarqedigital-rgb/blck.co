<?php
class Producto extends Model
{
    public function listar(string $q = ''): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria
                FROM producto p JOIN categoria c ON c.id = p.categoria_id
                WHERE p.nombre LIKE ?
                ORDER BY c.nombre, p.nombre";
        return $this->query($sql, ["%$q%"])->fetchAll();
    }

    public function menuPublico(): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria
                FROM producto p JOIN categoria c ON c.id = p.categoria_id
                WHERE p.activo = 1 ORDER BY c.nombre, p.nombre";
        return $this->query($sql)->fetchAll();
    }

    public function disponibles(): array
    {
        return $this->query("SELECT id, nombre, precio, stock FROM producto
                             WHERE activo = 1 AND stock > 0 ORDER BY nombre")->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        return $this->query("SELECT * FROM producto WHERE id = ?", [$id])->fetch() ?: null;
    }

    public function crear(array $d): void
    {
        $this->query("INSERT INTO producto (nombre, descripcion, precio, stock, categoria_id, activo)
                      VALUES (?,?,?,?,?,?)",
            [$d['nombre'], $d['descripcion'], $d['precio'], $d['stock'], $d['categoria_id'], $d['activo']]);
    }

    public function actualizar(int $id, array $d): void
    {
        $this->query("UPDATE producto SET nombre=?, descripcion=?, precio=?, stock=?, categoria_id=?, activo=?
                      WHERE id = ?",
            [$d['nombre'], $d['descripcion'], $d['precio'], $d['stock'], $d['categoria_id'], $d['activo'], $id]);
    }

    public function eliminar(int $id): bool
    {
        $n = $this->query("SELECT COUNT(*) FROM detalle_pedido WHERE producto_id = ?", [$id])->fetchColumn();
        if ($n > 0) {   // tiene historial: solo se desactiva
            $this->query("UPDATE producto SET activo = 0 WHERE id = ?", [$id]);
            return false;
        }
        $this->query("DELETE FROM producto WHERE id = ?", [$id]);
        return true;
    }
}
