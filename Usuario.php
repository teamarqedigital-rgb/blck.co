<?php
class Usuario extends Model
{
    private const ROLES_APLICACION = ['administrador', 'barista'];

    public function porCorreo(string $correo): ?array
    {
        $sql = "SELECT u.id, u.nombre, u.correo, u.clave, r.nombre AS rol
                FROM usuario u JOIN rol r ON r.id = u.rol_id
                WHERE u.correo = ? AND u.activo = 1
                  AND r.nombre IN ('administrador', 'barista')";
        return $this->query($sql, [$correo])->fetch() ?: null;
    }

    public function paraSesion(int $id): ?array
    {
        $sql = "SELECT u.id, u.nombre, r.nombre AS rol
                FROM usuario u JOIN rol r ON r.id = u.rol_id
                WHERE u.id = ? AND u.activo = 1
                  AND r.nombre IN ('administrador', 'barista')";
        return $this->query($sql, [$id])->fetch() ?: null;
    }

    public function listarGestionables(): array
    {
        $sql = "SELECT u.id, u.nombre, u.correo, u.activo, u.rol_id, r.nombre AS rol
                FROM usuario u JOIN rol r ON r.id = u.rol_id
                WHERE r.nombre IN ('administrador', 'barista')
                ORDER BY u.nombre, u.id";
        return $this->query($sql)->fetchAll();
    }

    public function buscarGestionable(int $id): ?array
    {
        $sql = "SELECT u.id, u.nombre, u.correo, u.activo, u.rol_id, r.nombre AS rol
                FROM usuario u JOIN rol r ON r.id = u.rol_id
                WHERE u.id = ? AND r.nombre IN ('administrador', 'barista')";
        return $this->query($sql, [$id])->fetch() ?: null;
    }

    public function rolesDisponibles(): array
    {
        return $this->query(
            "SELECT id, nombre FROM rol
             WHERE nombre IN ('administrador', 'barista')
             ORDER BY FIELD(nombre, 'administrador', 'barista')"
        )->fetchAll();
    }

    public function correoExiste(string $correo, int $exceptoId = 0): bool
    {
        return (bool)$this->query(
            'SELECT 1 FROM usuario WHERE correo = ? AND id <> ? LIMIT 1',
            [$correo, $exceptoId]
        )->fetchColumn();
    }

    public function crearGestionable(array $datos): void
    {
        $this->query(
            'INSERT INTO usuario (nombre, correo, clave, rol_id, activo) VALUES (?, ?, ?, ?, ?)',
            [
                $datos['nombre'],
                $datos['correo'],
                password_hash($datos['clave'], PASSWORD_DEFAULT),
                $datos['rol_id'],
                $datos['activo'],
            ]
        );
    }

    public function actualizarGestionable(int $id, array $datos, ?string $clave = null): void
    {
        $this->db->beginTransaction();
        try {
            $rolAdmin = $this->query(
                "SELECT id FROM rol WHERE nombre = 'administrador' FOR UPDATE"
            )->fetchColumn();
            if (!$rolAdmin) {
                throw new DomainException('No existe el rol administrador en la base de datos.');
            }

            $actual = $this->query(
                'SELECT rol_id, activo FROM usuario WHERE id = ? FOR UPDATE',
                [$id]
            )->fetch();
            if (!$actual) {
                throw new DomainException('La cuenta ya no existe.');
            }

            $nuevoRol = $this->query('SELECT nombre FROM rol WHERE id = ?', [$datos['rol_id']])->fetchColumn();
            if (!in_array($nuevoRol, self::ROLES_APLICACION, true)) {
                throw new DomainException('El rol seleccionado no está disponible.');
            }

            $eraAdminActivo = (int)$actual['rol_id'] === (int)$rolAdmin && (int)$actual['activo'] === 1;
            $seguiraAdminActivo = (int)$datos['rol_id'] === (int)$rolAdmin && (int)$datos['activo'] === 1;
            if ($eraAdminActivo && !$seguiraAdminActivo) {
                $administradoresActivos = (int)$this->query(
                    'SELECT COUNT(*) FROM usuario WHERE rol_id = ? AND activo = 1',
                    [(int)$rolAdmin]
                )->fetchColumn();
                if ($administradoresActivos <= 1) {
                    throw new DomainException('Debe quedar al menos un administrador activo.');
                }
            }

            $sql = 'UPDATE usuario SET nombre = ?, correo = ?, rol_id = ?, activo = ?';
            $params = [$datos['nombre'], $datos['correo'], $datos['rol_id'], $datos['activo']];
            if ($clave !== null) {
                $sql .= ', clave = ?';
                $params[] = password_hash($clave, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE id = ?';
            $params[] = $id;
            $this->query($sql, $params);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
