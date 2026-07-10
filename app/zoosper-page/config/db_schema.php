<?php

declare(strict_types=1);

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
    ],
];
