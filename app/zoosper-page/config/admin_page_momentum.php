<?php

declare(strict_types=1);

return [
    'page_momentum' => [
        'enabled' => false,
        'title' => 'Page momentum',
        'description' => 'Launch-readiness status for visible page/admin improvements.',
        'items' => [
            'page_renderer_candidate' => [
                'label' => 'PageRenderer report-only candidate planned',
                'status' => 'planned',
            ],
            'page_admin_visible_slice' => [
                'label' => 'Visible page admin momentum slice pending wiring',
                'status' => 'ready-for-wiring',
            ],
            'core_decoupling_readiness' => [
                'label' => 'Core decoupling readiness closed',
                'status' => 'complete',
            ],
        ],
    ],
];
