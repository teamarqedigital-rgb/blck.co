CREATE TABLE blck_os_audit_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    entity_type VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    details JSON NOT NULL,
    occurred_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY ix_blck_os_audit_events_occurred_at (occurred_at, id),
    KEY ix_blck_os_audit_events_action_occurred (action, occurred_at, id),
    KEY ix_blck_os_audit_events_actor_occurred (actor_user_id, occurred_at, id),
    KEY ix_blck_os_audit_events_entity (entity_type, entity_id, id),
    CONSTRAINT fk_blck_os_audit_events_actor
        FOREIGN KEY (actor_user_id) REFERENCES blck_os_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
