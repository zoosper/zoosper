CREATE TABLE IF NOT EXISTS entity_extension_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(120) NOT NULL,
    field_name VARCHAR(120) NOT NULL,
    value_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_entity_extension_field (entity_type, entity_id, module, field_name),
    KEY idx_entity_extension_lookup (entity_type, entity_id),
    KEY idx_entity_extension_module (module)
);
