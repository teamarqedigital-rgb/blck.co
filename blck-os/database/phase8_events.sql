CREATE TABLE blck_os_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_type VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    venue VARCHAR(200) NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    capacity INT UNSIGNED NOT NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'DRAFT',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_blck_os_events_status_starts_at (status, starts_at),
    KEY ix_blck_os_events_type_starts_at (event_type, starts_at),
    KEY ix_blck_os_events_created_by (created_by),
    CONSTRAINT ck_blck_os_events_type CHECK (
        event_type IN ('DJ_SESSION', 'MORNING_BEATS_COFFEE', 'WORKSHOP', 'PRIVATE_EVENT')
    ),
    CONSTRAINT ck_blck_os_events_status CHECK (status IN ('DRAFT', 'PUBLISHED', 'CANCELLED')),
    CONSTRAINT ck_blck_os_events_capacity CHECK (capacity > 0),
    CONSTRAINT ck_blck_os_events_time CHECK (ends_at > starts_at),
    CONSTRAINT fk_blck_os_events_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blck_os_event_reservations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id BIGINT UNSIGNED NOT NULL,
    reservation_code VARCHAR(32) NULL,
    qr_token CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    guest_name VARCHAR(160) NOT NULL,
    guest_email VARCHAR(254) NOT NULL,
    guest_phone VARCHAR(40) NOT NULL DEFAULT '',
    guest_count INT UNSIGNED NOT NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'CONFIRMED',
    created_by BIGINT UNSIGNED NULL,
    checked_in_by BIGINT UNSIGNED NULL,
    checked_in_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blck_os_event_reservations_code (reservation_code),
    UNIQUE KEY uq_blck_os_event_reservations_qr_token (qr_token),
    KEY ix_blck_os_event_reservations_event_status (event_id, status),
    KEY ix_blck_os_event_reservations_guest_email (guest_email),
    KEY ix_blck_os_event_reservations_created_by (created_by),
    KEY ix_blck_os_event_reservations_checked_in_by (checked_in_by),
    CONSTRAINT ck_blck_os_event_reservations_count CHECK (guest_count > 0),
    CONSTRAINT ck_blck_os_event_reservations_status CHECK (status IN ('CONFIRMED', 'CANCELLED', 'CHECKED_IN')),
    CONSTRAINT ck_blck_os_event_reservations_check_in CHECK (
        (status = 'CHECKED_IN' AND checked_in_at IS NOT NULL)
        OR (status <> 'CHECKED_IN' AND checked_in_at IS NULL AND checked_in_by IS NULL)
    ),
    CONSTRAINT fk_blck_os_event_reservations_event
        FOREIGN KEY (event_id) REFERENCES blck_os_events (id) ON DELETE RESTRICT,
    CONSTRAINT fk_blck_os_event_reservations_created_by
        FOREIGN KEY (created_by) REFERENCES blck_os_users (id) ON DELETE SET NULL,
    CONSTRAINT fk_blck_os_event_reservations_checked_in_by
        FOREIGN KEY (checked_in_by) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
