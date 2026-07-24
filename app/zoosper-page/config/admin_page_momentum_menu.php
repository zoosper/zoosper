<?php

declare(strict_types=1);

return [
    'page_momentum_menu' => [
        'enabled' => false,
        'items' => [
            [
                'label' => 'Page momentum',
                'route' => 'admin.page_momentum.index',
                'permission' => 'page.manage',
                'sort_order' => 95,
                'description' => 'Visible launch-readiness status for page/admin improvements.',
            ],
        ],
    ],
];
