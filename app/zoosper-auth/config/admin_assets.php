<?php

declare(strict_types=1);

return [
    'zoosper-permission-explorer-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/permission-explorer.css?v=fcb99090ad65',
        'screens' => ['admin-users'],
        'sort_order' => 86,
    ],
    'zoosper-permission-explorer-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/permission-explorer.js?v=9beeafa105ce',
        'screens' => ['admin-users'],
        'attributes' => ['defer' => true],
        'sort_order' => 86,
    ],
    'zoosper-personal-access-tokens-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/personal-access-tokens.css?v=621422fcf72a',
        'screens' => ['access-tokens'],
        'sort_order' => 88,
    ],
    'zoosper-personal-access-tokens-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/personal-access-tokens.js?v=2570d94da7cf',
        'screens' => ['access-tokens'],
        'sort_order' => 88,
        'attributes' => [
            'defer' => true,
        ],
    ],
    'zoosper-admin-user-workspace-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/admin-user-workspace.css?v=ef44599fd0c3',
        'sort_order' => 87,
        'screens' => ['admin-users'],
    ],
    'zoosper-admin-users-workspace-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/admin-users-workspace.js?v=55caef1d8d48',
        'sort_order' => 87,
        'screens' => ['admin-users'],
        'attributes' => ['defer' => true],
    ],
    'zoosper-admin-user-two-factor-reset-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/admin-user-two-factor-reset.js',
        'sort_order' => 87,
        'screens' => ['admin-users'],
    ],
    'zoosper-admin-role-user-assignment-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/user-assignment.css',
        'sort_order' => 87,
        'screens' => ['admin-roles'],
    ],
    'zoosper-admin-role-user-assignment-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/user-assignment.js',
        'sort_order' => 87,
        'screens' => ['admin-roles'],
        'attributes' => [
            'defer' => true,
        ],
    ],
];










