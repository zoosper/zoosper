<?php

declare(strict_types=1);

return [
    'page_momentum_routes' => [
        'enabled' => true,
        'routes' => [
            [
                'name' => 'admin.page_momentum.index',
                'method' => 'GET',
                'path' => '/admin/page-momentum',
                'controller' => Zoosper\Page\Admin\Controller\PageMomentumAdminController::class,
                'action' => 'index',
                'view' => 'admin/page-momentum.latte',
                'permission' => 'page.manage',
                'description' => 'Read-only launch-readiness panel for page/admin momentum.',
            ],
        ],
    ],
];
