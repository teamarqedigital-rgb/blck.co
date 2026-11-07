CREATE TABLE blck_os_roles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(32) NOT NULL,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_permissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    permission_key VARCHAR(80) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_permissions_key (permission_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_blck_os_role_permissions_role
        FOREIGN KEY (role_id) REFERENCES blck_os_roles (id) ON DELETE CASCADE,
    CONSTRAINT fk_blck_os_role_permissions_permission
        FOREIGN KEY (permission_id) REFERENCES blck_os_permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO blck_os_roles (code, name, description) VALUES
    ('OWNER', 'Propietario', 'Acceso total a BLCK.OS, incluida la administración de roles y permisos.'),
    ('ADMINISTRADOR', 'Administrador', 'Administra la operación y los usuarios, sin administrar roles.'),
    ('EMPLEADO', 'Empleado', 'Acceso a las funciones operativas asignadas.'),
    ('CLIENTE', 'Cliente', 'Acceso a su cuenta y a la consulta de eventos.')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);

INSERT INTO blck_os_permissions (permission_key, name, description) VALUES
    ('system.access', 'Acceder al sistema', 'Ingresar a las áreas autenticadas de BLCK.OS.'),
    ('account.password.change', 'Cambiar contraseña propia', 'Cambiar la contraseña de la propia cuenta.'),
    ('users.view', 'Consultar usuarios', 'Consultar cuentas de usuario.'),
    ('users.manage', 'Administrar usuarios', 'Crear, modificar, activar y desactivar cuentas.'),
    ('roles.manage', 'Administrar roles y permisos', 'Administrar la asignación de roles y permisos.'),
    ('inventory.view', 'Consultar inventario', 'Consultar categorías, productos y existencias.'),
    ('inventory.manage', 'Administrar inventario', 'Administrar productos, categorías y movimientos de inventario.'),
    ('sales.view', 'Consultar ventas', 'Consultar ventas y transacciones.'),
    ('sales.manage', 'Administrar ventas', 'Registrar ventas, pagos y anulaciones permitidas.'),
    ('events.view', 'Consultar eventos', 'Consultar eventos publicados y activos.'),
    ('events.manage', 'Administrar eventos', 'Administrar eventos, sesiones, reservas y accesos.'),
    ('crm.view', 'Consultar clientes', 'Consultar perfiles e historial de clientes.'),
    ('crm.manage', 'Administrar clientes', 'Administrar perfiles, membresías y fidelización.'),
    ('reports.view', 'Consultar reportes', 'Consultar reportes operativos y ejecutivos.'),
    ('audit.view', 'Consultar auditoría', 'Consultar el registro de auditoría.')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);

INSERT INTO blck_os_role_permissions (role_id, permission_id)
SELECT role_row.id, permission_row.id
FROM blck_os_roles AS role_row
CROSS JOIN blck_os_permissions AS permission_row
WHERE role_row.code = 'OWNER'
ON DUPLICATE KEY UPDATE permission_id = VALUES(permission_id);

INSERT INTO blck_os_role_permissions (role_id, permission_id)
SELECT role_row.id, permission_row.id
FROM blck_os_roles AS role_row
INNER JOIN blck_os_permissions AS permission_row
    ON permission_row.permission_key IN (
        'system.access',
        'account.password.change',
        'users.view',
        'users.manage',
        'inventory.view',
        'inventory.manage',
        'sales.view',
        'sales.manage',
        'events.view',
        'events.manage',
        'crm.view',
        'crm.manage',
        'reports.view',
        'audit.view'
    )
WHERE role_row.code = 'ADMINISTRADOR'
ON DUPLICATE KEY UPDATE permission_id = VALUES(permission_id);

INSERT INTO blck_os_role_permissions (role_id, permission_id)
SELECT role_row.id, permission_row.id
FROM blck_os_roles AS role_row
INNER JOIN blck_os_permissions AS permission_row
    ON permission_row.permission_key IN (
        'system.access',
        'account.password.change',
        'inventory.view',
        'sales.view',
        'sales.manage',
        'events.view',
        'events.manage',
        'crm.view'
    )
WHERE role_row.code = 'EMPLEADO'
ON DUPLICATE KEY UPDATE permission_id = VALUES(permission_id);

INSERT INTO blck_os_role_permissions (role_id, permission_id)
SELECT role_row.id, permission_row.id
FROM blck_os_roles AS role_row
INNER JOIN blck_os_permissions AS permission_row
    ON permission_row.permission_key IN (
        'system.access',
        'account.password.change',
        'events.view'
    )
WHERE role_row.code = 'CLIENTE'
ON DUPLICATE KEY UPDATE permission_id = VALUES(permission_id);

ALTER TABLE blck_os_users
    ADD COLUMN role_id INT UNSIGNED NULL AFTER auth_version,
    ADD KEY ix_blck_os_users_role (role_id),
    ADD CONSTRAINT fk_blck_os_users_role
        FOREIGN KEY (role_id) REFERENCES blck_os_roles (id) ON DELETE RESTRICT;
