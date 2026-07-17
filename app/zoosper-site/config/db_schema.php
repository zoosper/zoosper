<?php

declare(strict_types=1);

/*
 * Phase 1.34b: the sites table is enriched with the store-view dimensions the
 * rich SiteContext needs (locale, currency, base_url, website/store/store-view
 * codes, path_prefix). These are additive columns applied by the unified schema
 * engine; existing installs gain them idempotently with safe defaults, so the
 * DB (SiteRepository) can become the single source of truth in Phase 1.34c.
 */
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
    ],
];