CREATE TABLE blck_os_sales (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_number VARCHAR(32) NULL,
    submission_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    subtotal DECIMAL(12,2) UNSIGNED NOT NULL,
    total DECIMAL(12,2) UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_sales_ticket_number (ticket_number),
    UNIQUE KEY uq_blck_os_sales_submission_key (submission_key),
    KEY ix_blck_os_sales_created_at (created_at),
    KEY ix_blck_os_sales_created_by (created_by),
    CONSTRAINT fk_blck_os_sales_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_sale_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sale_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    product_name VARCHAR(160) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) UNSIGNED NOT NULL,
    line_total DECIMAL(12,2) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_sale_items_sale_product (sale_id, product_id),
    KEY ix_blck_os_sale_items_product (product_id),
    CONSTRAINT ck_blck_os_sale_items_quantity CHECK (quantity > 0),
    CONSTRAINT fk_blck_os_sale_items_sale
        FOREIGN KEY (sale_id) REFERENCES blck_os_sales (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_sale_items_product
        FOREIGN KEY (product_id) REFERENCES blck_os_inventory_products (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sale_id BIGINT UNSIGNED NOT NULL,
    payment_method VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    amount DECIMAL(12,2) UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_blck_os_payments_sale (sale_id),
    KEY ix_blck_os_payments_created_at (created_at),
    CONSTRAINT ck_blck_os_payments_method CHECK (payment_method IN ('CASH', 'CARD', 'TRANSFER')),
    CONSTRAINT ck_blck_os_payments_amount CHECK (amount > 0),
    CONSTRAINT fk_blck_os_payments_sale
        FOREIGN KEY (sale_id) REFERENCES blck_os_sales (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_payments_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE blck_os_inventory_movements
    ADD COLUMN sale_id BIGINT UNSIGNED NULL AFTER created_by,
    ADD KEY ix_blck_os_inventory_movements_sale (sale_id),
    ADD CONSTRAINT fk_blck_os_inventory_movements_sale
        FOREIGN KEY (sale_id) REFERENCES blck_os_sales (id) ON DELETE RESTRICT;
