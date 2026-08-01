<?php

declare(strict_types=1);

return [
    'stylesheets' => [
        [
            'path' => 'resources/admin/css/grid-workspace.css',
            'priority' => 70,
        ],
        [
            'path' => 'resources/admin/css/grid-workspace-status.css',
            'priority' => 71,
        ],
        [
            'path' => 'resources/admin/css/grid-workspace-view-actions.css',
            'priority' => 72,
        ],
        [
            'path' => 'resources/admin/css/grid-workspace-live.css',
            'priority' => 73,
        ],
        [
            'path' => 'resources/admin/css/grid-compact-workspace.css',
            'priority' => 80,
        ],
    ],
    'scripts' => [
        [
            'path' => 'resources/admin/js/grid-workspace.js',
            'priority' => 70,
            'defer' => true,
        ],
        [
            'path' => 'resources/admin/js/grid-workspace-view-actions.js',
            'priority' => 73,
            'defer' => true,
        ],
        [
            'path' => 'resources/admin/js/grid-workspace-page-size.js',
            'priority' => 74,
            'defer' => true,
        ],
        [
            'path' => 'resources/admin/js/grid-compact-workspace.js',
            'priority' => 80,
            'defer' => true,
        ],
    ],
];
