<?php

declare(strict_types=1);

$cssFile = __DIR__ . '/../resources/assets/css/announcement-modal.css';
$jsFile = __DIR__ . '/../resources/assets/js/announcement-modal.js';

$cssVersion = is_file($cssFile) ? substr((string) hash_file('sha256', $cssFile), 0, 12) : '1';
$jsVersion = is_file($jsFile) ? substr((string) hash_file('sha256', $jsFile), 0, 12) : '1';

return [
    'assets' => [
        'zoosper-global-announcements-modal-style' => [
            'type' => 'style',
            'path' => '/asset/zoosper-global-announcements/css/announcement-modal.css?v=' . $cssVersion,
            'sort_order' => 20,
        ],
        'zoosper-global-announcements-modal-script' => [
            'type' => 'script',
            'path' => '/asset/zoosper-global-announcements/js/announcement-modal.js?v=' . $jsVersion,
            'sort_order' => 20,
            'defer' => true,
        ],
    ],
];
