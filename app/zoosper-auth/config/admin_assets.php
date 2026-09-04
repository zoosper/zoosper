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
        'path' => '/assets/admin/js/personal-access-tokens.js?v=79ede8c2c657',
        'screens' => ['access-tokens'],
        'sort_order' => 88,
        'attributes' => [
            'defer' => true,
        ],
    ],
    'zoosper-admin-user-workspace-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/admin-user-workspace.css?v=c0f079e3a3ef',
        'sort_order' => 87,
        'screens' => ['admin-users'],
    ],
    'zoosper-admin-users-workspace-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/admin-users-workspace.js?v=4daa665ebe69',
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
    'zoosper-roles-workspace-style' => [
        'type' => 'style',
        'path' => '/assets/admin/css/roles-workspace.css?v=db715d0965e4',
        'sort_order' => 87,
        'screens' => ['admin-roles'],
    ],
    'zoosper-roles-workspace-runtime' => [
        'type' => 'script',
        'path' => '/assets/admin/js/roles-workspace.js?v=84666a3651ba',
        'sort_order' => 87,
        'screens' => ['admin-roles'],
        'attributes' => ['defer' => true],
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










