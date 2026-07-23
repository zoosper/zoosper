<?php

declare(strict_types=1);

return [
    'enabled' => false,
    'mode' => 'report_only',
    'report_path' => 'var/reports/rate-limit-events.jsonl',
    'identity_salt' => '',
    'policies' => [
        // Example future policy shape:
        // 'admin.login' => [
        //     'scope' => 'admin',
        //     'max_attempts' => 5,
        //     'window_seconds' => 300,
        // ],
    ],
];
