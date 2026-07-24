<?php

declare(strict_types=1);

return [
    'page_momentum_runtime_aggregation_candidate' => [
        'enabled' => true,
        'provider' => Zoosper\Page\Admin\PageMomentumAdminRuntimeAggregationProvider::class,
        'adapter' => Zoosper\Page\Admin\PageMomentumAdminSourceHookAdapter::class,
        'hook_candidate_config' => 'admin_page_momentum_hook_candidate.php',
        'route' => 'admin.page_momentum.index',
        'path' => '/admin/page-momentum',
        'permission' => 'page.manage',
        'live_mutation' => false,
    ],
];
