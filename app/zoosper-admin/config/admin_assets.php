<?php

declare(strict_types=1);

$assetVersion = '1.37l';

return [
    'assets' => [
        'zoosper-admin-base' => [
            'type' => 'style',
            'path' => '/assets/admin/css/admin.css?v=' . $assetVersion,
            'sort_order' => 10,
        ],
        'zoosper-admin-messages-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-admin-messages.css?v=' . $assetVersion,
            'sort_order' => 20,
        ],
        'zoosper-admin-editor-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-content-editor.css?v=' . $assetVersion,
            'sort_order' => 30,
        ],
        'zoosper-admin-messages-script' => [
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-admin-messages.js?v=' . $assetVersion,
            'sort_order' => 20,
            'attributes' => ['defer' => true],
        ],
        'zoosper-admin-editorjs-bundle' => [
            'type' => 'script',
            'path' => '/assets/admin/js/editorjs.bundle.js?v=' . $assetVersion,
            'sort_order' => 25,
            'attributes' => ['defer' => true],
        ],
        'zoosper-admin-editor-script' => [
            'type' => 'script',
            'path' => '/assets/admin/js/zoosper-content-editor.js?v=' . $assetVersion,
            'sort_order' => 30,
            'attributes' => ['defer' => true],
        ],
    ],
];
