<?php

declare(strict_types=1);

return [
    'assets' => [
        'zoosper-admin-editor-style' => [
            'screens' => ['pages'],
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-content-editor.css?v=1.37l',
            'sort_order' => 30,
        ],
        'zoosper-admin-editorjs-bundle' => [
            'screens' => ['pages'],
            'type' => 'script',
            'path' => '/assets/admin/js/editorjs.bundle.js?v=1.37l',
            'sort_order' => 25,
            'attributes' => [
                'defer' => true,
            ],
        ],
        'zoosper-admin-editor-script' => [
            'screens' => ['pages'],
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-content-editor.js?v=1.37l',
            'sort_order' => 30,
            'attributes' => [
                'defer' => true,
            ],
        ],
    ],
];
