<?php

declare(strict_types=1);

return [
    'tables' => [
        'sites' => [
            'columns' => [
                'theme_code' => ['type' => 'string', 'length' => 120, 'nullable' => false, 'default' => 'default'],
                'locale' => ['type' => 'string', 'length' => 16, 'nullable' => false, 'default' => 'en_AU'],
                'currency' => ['type' => 'string', 'length' => 8, 'nullable' => false, 'default' => 'AUD'],
                'base_url' => ['type' => 'string', 'length' => 255, 'nullable' => false, 'default' => ''],
                'website_code' => ['type' => 'string', 'length' => 64, 'nullable' => false, 'default' => 'main'],
                'store_code' => ['type' => 'string', 'length' => 64, 'nullable' => false, 'default' => 'main'],
                'store_view_code' => ['type' => 'string', 'length' => 64, 'nullable' => false, 'default' => 'default'],
                'path_prefix' => ['type' => 'string', 'length' => 190, 'nullable' => false, 'default' => ''],
            ],
            'indexes' => [
                'idx_sites_theme_code' => ['columns' => ['theme_code']],
                'idx_sites_store_view_code' => ['columns' => ['store_view_code']],
            ],
        ],
        'site_domains' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'site_id' => ['type' => 'integer', 'nullable' => false],
                'host' => ['type' => 'string', 'length' => 190, 'nullable' => false],
                'is_primary' => ['type' => 'integer', 'nullable' => false, 'default' => 0],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_site_domains_site_id' => ['columns' => ['site_id']],
                'uniq_site_domains_host' => ['columns' => ['host'], 'unique' => true],
            ],
        ],
    ],
];
