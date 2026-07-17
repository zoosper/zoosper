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
            ],
        ],
    ],
];
