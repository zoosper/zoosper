-- Reference SQL for the portable rate limit bucket store.
-- This file is documentation/reference for Phase 1.39g-i and is not automatically executed.

CREATE TABLE rate_limit_buckets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scope VARCHAR(120) NOT NULL,
    identity_hash VARCHAR(128) NOT NULL,
    rule_key VARCHAR(120) NOT NULL,
    window_starts_at INTEGER NOT NULL,
    window_ends_at INTEGER NOT NULL,
    attempts INTEGER NOT NULL DEFAULT 0,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
);

CREATE UNIQUE INDEX rate_limit_buckets_unique_window
    ON rate_limit_buckets (scope, identity_hash, rule_key, window_starts_at);

CREATE INDEX rate_limit_buckets_expires_idx
    ON rate_limit_buckets (window_ends_at);
