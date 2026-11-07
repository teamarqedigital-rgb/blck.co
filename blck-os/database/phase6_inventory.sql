CREATE TABLE blck_os_inventory_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_inventory_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_inventory_subcategories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_inventory_subcategories_category_name (category_id, name),
    CONSTRAINT fk_blck_os_inventory_subcategories_category
        FOREIGN KEY (category_id) REFERENCES blck_os_inventory_categories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_inventory_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sku VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    name VARCHAR(160) NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    subcategory_id BIGINT UNSIGNED NULL,
    unit_price DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_inventory_products_sku (sku),
    KEY ix_blck_os_inventory_products_category (category_id),
    KEY ix_blck_os_inventory_products_subcategory (subcategory_id),
    CONSTRAINT fk_blck_os_inventory_products_category
        FOREIGN KEY (category_id) REFERENCES blck_os_inventory_categories (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_inventory_products_subcategory
        FOREIGN KEY (subcategory_id) REFERENCES blck_os_inventory_subcategories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_inventory_movements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    movement_type VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    quantity INT NOT NULL,
    stock_before INT UNSIGNED NOT NULL,
    stock_after INT UNSIGNED NOT NULL,
    note VARCHAR(255) NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_blck_os_inventory_movements_product_created (product_id, created_at),
    KEY ix_blck_os_inventory_movements_created_by (created_by),
    CONSTRAINT ck_blck_os_inventory_movements_type
        CHECK (movement_type IN ('IN', 'OUT', 'ADJUSTMENT')),
    CONSTRAINT ck_blck_os_inventory_movements_quantity
        CHECK (
            (movement_type = 'IN' AND quantity > 0)
            OR (movement_type = 'OUT' AND quantity < 0)
            OR (movement_type = 'ADJUSTMENT' AND quantity <> 0)
        ),
    CONSTRAINT fk_blck_os_inventory_movements_product
        FOREIGN KEY (product_id) REFERENCES blck_os_inventory_products (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_inventory_movements_user
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
