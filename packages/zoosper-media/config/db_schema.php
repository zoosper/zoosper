<?php

declare(strict_types=1);

/**
 * Media module declarative schema.
 *
 * Uploaded originals are stored outside public/ under storage/media/original.
 * Public browser URLs point only to validated copies under public/media.
 */
return [
    'tables' => [
        'media_assets' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'uuid' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'filename' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'original_filename' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'mime_type' => ['type' => 'string', 'length' => 120, 'nullable' => false],
                'extension' => ['type' => 'string', 'length' => 16, 'nullable' => false],
                'size_bytes' => ['type' => 'integer', 'nullable' => false],
                'storage_path' => ['type' => 'string', 'length' => 500, 'nullable' => false],
                'public_path' => ['type' => 'string', 'length' => 500, 'nullable' => true],
                'status' => ['type' => 'string', 'length' => 32, 'nullable' => false, 'default' => 'active'],
                'created_by' => ['type' => 'integer', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'uq_media_assets_uuid' => ['columns' => ['uuid'], 'unique' => true],
                'idx_media_assets_status' => ['columns' => ['status']],
                'idx_media_assets_mime' => ['columns' => ['mime_type']],
                'idx_media_assets_created' => ['columns' => ['created_at']],
                'idx_media_assets_creator' => ['columns' => ['created_by']],
            ],
            'foreign_keys' => [
                'fk_media_assets_creator' => ['columns' => ['created_by'], 'referenced_table' => 'admin_users', 'referenced_columns' => ['id'], 'on_delete' => 'SET NULL'],
            ],
        ],
        'media_processing_queue' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'asset_id' => ['type' => 'integer', 'nullable' => false],
                'plan_json' => ['type' => 'text', 'nullable' => false],
                'status' => ['type' => 'string', 'length' => 32, 'nullable' => false, 'default' => 'pending'],
                'attempts' => ['type' => 'integer', 'nullable' => false, 'default' => 0],
                'error_message' => ['type' => 'text', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_media_queue_status' => ['columns' => ['status']],
            ],
            'foreign_keys' => [
                'fk_media_processing_queue_asset' => ['columns' => ['asset_id'], 'referenced_table' => 'media_assets', 'referenced_columns' => ['id'], 'on_delete' => 'CASCADE', 'on_update' => 'NO ACTION'],
            ],
        ],
        'media_derivatives' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'media_asset_id' => ['type' => 'integer', 'nullable' => false],
                'profile' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'format' => ['type' => 'string', 'length' => 16, 'nullable' => false],
                'width' => ['type' => 'integer', 'nullable' => false],
                'height' => ['type' => 'integer', 'nullable' => false],
                'size_bytes' => ['type' => 'integer', 'nullable' => false],
                'storage_path' => ['type' => 'string', 'length' => 500, 'nullable' => false],
                'public_path' => ['type' => 'string', 'length' => 500, 'nullable' => false],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'uq_media_derivatives_asset_profile_format' => ['columns' => ['media_asset_id', 'profile', 'format'], 'unique' => true],
                'idx_media_derivatives_asset' => ['columns' => ['media_asset_id']],
            ],
            'foreign_keys' => [
                'fk_media_derivatives_asset' => ['columns' => ['media_asset_id'], 'referenced_table' => 'media_assets', 'referenced_columns' => ['id'], 'on_delete' => 'CASCADE', 'on_update' => 'CASCADE'],
            ],
        ],
    ],
];











