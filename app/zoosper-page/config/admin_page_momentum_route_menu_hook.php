<?php

declare(strict_types=1);

return [
    'page_momentum_route_menu_hook' => [
        'enabled' => true,
        'hook' => Zoosper\Page\Admin\PageMomentumAdminRouteMenuHook::class,
        'runtime_config' => 'admin_page_momentum_runtime_aggregation_candidate.php',
        'hook_candidate_config' => 'admin_page_momentum_hook_candidate.php',
        'route' => 'admin.page_momentum.index',
        'path' => '/admin/page-momentum',
        'permission' => 'page.manage',
        'live_mutation' => false,
    ],
];
