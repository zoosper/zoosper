<?php

declare(strict_types=1);

return [
    'tables' => [
        'sites' => [
            'columns' => [
                'theme_code' => ['type' => 'string', 'length' => 120, 'nullable' => false, 'default' => 'default'],
            ],
            'indexes' => [
                'idx_sites_theme_code' => ['columns' => ['theme_code']],
            ],
        ],
    ],
];
