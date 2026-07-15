<?php

declare(strict_types=1);

/**
 * Page module declarative schema.
 *
 * page_site_assignments is owned by this module. The `pages` base table is
 * created by a file migration; the columns declared here are ADDED to it
 * (SEO metadata + content format/json). The unified engine adds only missing
 * columns, so this is a no-op where they already exist.
 */
return [
    'tables' => [
        'page_site_assignments' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'page_id' => ['type' => 'integer', 'nullable' => false],
                'site_id' => ['type' => 'integer', 'nullable' => false],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'idx_page_site_assignments_page_site' => ['columns' => ['page_id', 'site_id'], 'unique' => true],
                'idx_page_site_assignments_site' => ['columns' => ['site_id']],
            ],
        ],
        'pages' => [
            'columns' => [
                'meta_title' => ['type' => 'string', 'length' => 255, 'nullable' => true],
                'meta_description' => ['type' => 'string', 'length' => 500, 'nullable' => true],
                'meta_keywords' => ['type' => 'string', 'length' => 500, 'nullable' => true],
                'canonical_url' => ['type' => 'string', 'length' => 500, 'nullable' => true],
                'content_format' => ['type' => 'string', 'length' => 32, 'nullable' => false, 'default' => 'html'],
                'content_json' => ['type' => 'json', 'nullable' => true],
            ],
        ],
    ],
];
