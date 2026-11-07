CREATE TABLE blck_os_customers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(40) NOT NULL DEFAULT '',
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'ACTIVE',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_customers_email (email),
    KEY ix_blck_os_customers_status_name (status, full_name),
    KEY ix_blck_os_customers_created_by (created_by),
    CONSTRAINT ck_blck_os_customers_status CHECK (status IN ('ACTIVE', 'INACTIVE')),
    CONSTRAINT fk_blck_os_customers_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_customer_notes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    note_text TEXT NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_blck_os_customer_notes_customer_created (customer_id, created_at),
    KEY ix_blck_os_customer_notes_created_by (created_by),
    CONSTRAINT fk_blck_os_customer_notes_customer
        FOREIGN KEY (customer_id) REFERENCES blck_os_customers (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_customer_notes_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_customer_memberships (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    membership_name VARCHAR(100) NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'ACTIVE',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_blck_os_customer_memberships_customer_status (customer_id, status),
    KEY ix_blck_os_customer_memberships_created_by (created_by),
    CONSTRAINT ck_blck_os_customer_memberships_status CHECK (status IN ('ACTIVE', 'INACTIVE')),
    CONSTRAINT ck_blck_os_customer_memberships_dates CHECK (ends_on IS NULL OR ends_on >= starts_on),
    CONSTRAINT fk_blck_os_customer_memberships_customer
        FOREIGN KEY (customer_id) REFERENCES blck_os_customers (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_customer_memberships_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_customer_points (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    points_delta BIGINT NOT NULL,
    entry_type VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_blck_os_customer_points_customer_created (customer_id, created_at),
    KEY ix_blck_os_customer_points_created_by (created_by),
    CONSTRAINT ck_blck_os_customer_points_entry CHECK (
        (entry_type = 'EARN' AND points_delta > 0)
        OR (entry_type = 'REDEEM' AND points_delta < 0)
    ),
    CONSTRAINT fk_blck_os_customer_points_customer
        FOREIGN KEY (customer_id) REFERENCES blck_os_customers (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_customer_points_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
