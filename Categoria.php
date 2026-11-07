<?php
class Categoria extends Model
{
    public function todas(): array
    {
        $sql = "SELECT c.*, COUNT(p.id) AS productos
                FROM categoria c LEFT JOIN producto p ON p.categoria_id = c.id
                GROUP BY c.id ORDER BY c.nombre";
        return $this->query($sql)->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        return $this->query("SELECT * FROM categoria WHERE id = ?", [$id])->fetch() ?: null;
    }

    public function existeNombre(string $nombre, int $excluirId = 0): bool
    {
        return (bool)$this->query("SELECT 1 FROM categoria WHERE nombre = ? AND id <> ?", [$nombre, $excluirId])->fetch();
    }

    public function crear(string $nombre): void
    {
        $this->query("INSERT INTO categoria (nombre) VALUES (?)", [$nombre]);
    }

    public function actualizar(int $id, string $nombre): void
    {
        $this->query("UPDATE categoria SET nombre = ? WHERE id = ?", [$nombre, $id]);
    }

    public function eliminar(int $id): bool
    {
        $n = $this->query("SELECT COUNT(*) FROM producto WHERE categoria_id = ?", [$id])->fetchColumn();
        if ($n > 0) {
            return false;   // regla: no borrar categorías con productos
        }
        $this->query("DELETE FROM categoria WHERE id = ?", [$id]);
        return true;
    }
}
