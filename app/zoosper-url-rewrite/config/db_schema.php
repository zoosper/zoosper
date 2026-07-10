<?php

declare(strict_types=1);

return [
    'tables' => [
        'url_rewrites' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'site_id' => ['type' => 'integer', 'nullable' => false],
                'request_path' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'target_path' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'entity_type' => ['type' => 'string', 'length' => 64, 'nullable' => false, 'default' => 'custom'],
                'entity_id' => ['type' => 'integer', 'nullable' => true],
                'redirect_type' => ['type' => 'integer', 'nullable' => false, 'default' => 301],
                'is_active' => ['type' => 'integer', 'nullable' => false, 'default' => 1],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'idx_url_rewrites_site_request_path' => ['columns' => ['site_id', 'request_path'], 'unique' => true],
                'idx_url_rewrites_entity' => ['columns' => ['entity_type', 'entity_id']],
            ],
        ],
    ],
];
