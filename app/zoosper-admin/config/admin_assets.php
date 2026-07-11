<?php

declare(strict_types=1);

return [
    'assets' => [
        'zoosper-admin-base' => [
            'type' => 'style',
            'path' => '/assets/admin/css/admin.css',
            'sort_order' => 10,
        ],
        'zoosper-admin-messages-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-admin-messages.css',
            'sort_order' => 20,
        ],
        'zoosper-admin-editor-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-content-editor.css',
            'sort_order' => 30,
        ],
        'zoosper-admin-messages-script' => [
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-admin-messages.js',
            'sort_order' => 20,
            'attributes' => ['defer' => true],
        ],
        'zoosper-admin-editor-script' => [
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-content-editor.js',
            'sort_order' => 30,
            'attributes' => ['defer' => true],
        ],
    ],
];
