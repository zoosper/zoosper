<?php

declare(strict_types=1);

return [
    'page_momentum_source_hook_adapter' => [
        'enabled' => true,
        'adapter' => Zoosper\Page\Admin\PageMomentumAdminSourceHookAdapter::class,
        'candidate_config' => 'admin_page_momentum_hook_candidate.php',
        'route' => 'admin.page_momentum.index',
        'permission' => 'page.manage',
        'live_mutation' => false,
    ],
];
