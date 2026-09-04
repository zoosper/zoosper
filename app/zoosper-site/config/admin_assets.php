<?php
declare(strict_types=1);
return [
    'assets' => [
        'zoosper-sites-workspace-style' => [
            'screens' => ['sites'],
            'type' => 'style',
            'path' => '/asset/zoosper-site/css/sites-workspace.css?v=7cbbcc3414f3',
            'sort_order' => 98,
        ],
        'zoosper-sites-workspace-script' => [
            'screens' => ['sites'],
            'type' => 'script',
            'path' => '/asset/zoosper-site/js/sites-workspace.js?v=0d65cc7bd27e',
            'sort_order' => 99,
            'attributes' => ['defer' => true],
        ],
        'zoosper-site-domains-workspace-style' => [
            'screens' => ['site-domains'],
            'type' => 'style',
            'path' => '/asset/zoosper-site/css/site-domains-workspace.css?v=26916ac7677a',
            'sort_order' => 98,
        ],
        'zoosper-site-domains-workspace-script' => [
            'screens' => ['site-domains'],
            'type' => 'script',
            'path' => '/asset/zoosper-site/js/site-domains-workspace.js?v=1451bd71eaa3',
            'sort_order' => 99,
            'attributes' => ['defer' => true],
        ],
    ],
];
