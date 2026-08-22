<?php

declare(strict_types=1);

return [
    'zoosper-permission-explorer-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/permission-explorer.css',
        'sort_order' => 86,
    ],
    'zoosper-permission-explorer-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/permission-explorer.js',
        'sort_order' => 86,
    ],
    'zoosper-personal-access-tokens-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/personal-access-tokens.css',
        'sort_order' => 88,
    ],
    'zoosper-personal-access-tokens-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/personal-access-tokens.js',
        'sort_order' => 88,
        'attributes' => [
            'defer' => true,
        ],
    ],
    'zoosper-admin-user-two-factor-reset-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/admin-user-two-factor-reset.js',
        'sort_order' => 87,
    ],
];
