<?php

declare(strict_types=1);

/**
 * Core module declarative schema.
 *
 * Owns infrastructure tables:
 *  - schema_snapshots: audit trail of applied declarative schema changes.
 *  - entity_extension_values: generic per-module extension-field store written by
 *    EntityExtensionDataPersister. Declaring it here means the unified schema
 *    engine creates it on `php bin/zoosper migrate` (previously it was only in a
 *    dead database/schema/*.sql file that nothing read, so a fresh install would
 *    throw "base table not found" the first time an extension field was saved).
 */
return [
    'tables' => [
        'schema_snapshots' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'schema_hash' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'statements_json' => ['type' => 'json', 'nullable' => false],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_schema_snapshots_hash' => ['columns' => ['schema_hash']],
                'idx_schema_snapshots_created' => ['columns' => ['created_at']],
            ],
        ],
        'entity_extension_values' => [
            'columns' => [
                'id' => ['type' => 'bigint', 'primary' => true, 'auto_increment' => true],
                'entity_type' => ['type' => 'string', 'length' => 100, 'nullable' => false],
                'entity_id' => ['type' => 'bigint', 'nullable' => false],
                'module' => ['type' => 'string', 'length' => 120, 'nullable' => false],
                'field_name' => ['type' => 'string', 'length' => 120, 'nullable' => false],
                'value_json' => ['type' => 'json', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'uq_entity_extension_field' => ['columns' => ['entity_type', 'entity_id', 'module', 'field_name'], 'unique' => true],
                'idx_entity_extension_lookup' => ['columns' => ['entity_type', 'entity_id']],
                'idx_entity_extension_module' => ['columns' => ['module']],
            ],
        ],
    ],
    'rate_limit_buckets' => [
        'columns' => [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'scope' => ['type' => 'string', 'length' => 120, 'nullable' => false],
            'identity_hash' => ['type' => 'string', 'length' => 128, 'nullable' => false],
            'rule_key' => ['type' => 'string', 'length' => 120, 'nullable' => false],
            'window_starts_at' => ['type' => 'integer', 'nullable' => false],
            'window_ends_at' => ['type' => 'integer', 'nullable' => false],
            'attempts' => ['type' => 'integer', 'nullable' => false, 'default' => 0],
            'created_at' => ['type' => 'integer', 'nullable' => false],
            'updated_at' => ['type' => 'integer', 'nullable' => false],
        ],
        'indexes' => [
            'rate_limit_buckets_unique_window' => [
                'columns' => ['scope', 'identity_hash', 'rule_key', 'window_starts_at'],
                'unique' => true,
            ],
            'rate_limit_buckets_expires_idx' => [
                'columns' => ['window_ends_at'],
            ],
        ],
    ],

];
