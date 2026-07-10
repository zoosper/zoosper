CREATE TABLE IF NOT EXISTS url_rewrites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    site_id INTEGER NOT NULL,
    request_path VARCHAR(255) NOT NULL,
    target_path VARCHAR(255) NOT NULL,
    entity_type VARCHAR(64) NOT NULL DEFAULT 'custom',
    entity_id INTEGER NULL,
    redirect_type INTEGER NOT NULL DEFAULT 301,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_url_rewrites_site_request_path
    ON url_rewrites (site_id, request_path);

CREATE INDEX IF NOT EXISTS idx_url_rewrites_entity
    ON url_rewrites (entity_type, entity_id);
