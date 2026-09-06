<?php

declare(strict_types=1);

return [
    'zoosper-mail-email-log-workspace-script' => [
        'type' => 'script',
        'path' => '/asset/zoosper-mail/js/email-log-workspace.js',
        'sort_order' => 100,
        'defer' => true,
        'screens' => ['mail-logs'],
    ],
    'zoosper-mail-email-log-style' => [
        'type' => 'style',
        'path' => '/asset/zoosper-mail/css/email-log.css',
        'screens' => ['mail-logs'],
    ],
];
