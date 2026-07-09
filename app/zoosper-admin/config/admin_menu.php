<?php

declare(strict_types=1);

return [
    [
        'code' => 'dashboard',
        'label' => 'Dashboard',
        'url' => '/admin',
        'permission' => 'admin.access',
        'sort_order' => 10,
        'group' => 'Content',
    ],
    [
        'code' => 'sites',
        'label' => 'Sites',
        'url' => '#',
        'permission' => 'settings.manage',
        'sort_order' => 80,
        'group' => 'System',
    ],
    [
        'code' => 'settings',
        'label' => 'Settings',
        'url' => '#',
        'permission' => 'settings.manage',
        'sort_order' => 90,
        'group' => 'System',
    ],
];
