<?php

declare(strict_types=1);

$assets = dirname(__DIR__) . '/resources/assets';
$version = static fn (string $path): string => substr(hash_file('sha256', $assets . '/' . $path) ?: 'dev', 0, 12);

return [
    'zoosper-settings-workspace-style' => [
        'type' => 'style',
        'path' => '/asset/zoosper-settings/css/settings-workspace.css?v=' . $version('css/settings-workspace.css'),
        'sort_order' => 90,
        'screens' => ['settings'],
    ],
    'zoosper-settings-workspace-script' => [
        'type' => 'script',
        'path' => '/asset/zoosper-settings/js/settings-workspace.js?v=' . $version('js/settings-workspace.js'),
        'sort_order' => 90,
        'screens' => ['settings'],
        'attributes' => ['defer' => true],
    ],
];
