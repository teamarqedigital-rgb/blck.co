<?php
// Clase base de los modelos: acceso a PDO.
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::conn();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $st = $this->db->prepare($sql);   // consultas preparadas siempre
        $st->execute($params);
        return $st;
    }
}
