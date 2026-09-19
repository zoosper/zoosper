<?php

declare(strict_types=1);

$stylesheet = dirname(__DIR__) . '/resources/admin/css/media-visual-grid.css';
$version = substr(hash_file('sha256', $stylesheet) ?: 'dev', 0, 12);

return [
    'assets' => [
        'media.editor-picker-style' => [
            'screens' => ['pages'],
            'type' => 'style',
            'path' => '/asset/zoosper-media/css/editor-media-picker.css?v=1.0.0',
            'sort_order' => 31,
        ],
        'media.editor-picker-script' => [
            'screens' => ['pages'],
            'type' => 'script',
            'path' => '/asset/zoosper-media/js/editor-media-picker.js?v=1.0.0',
            'sort_order' => 32,
            'attributes' => ['defer' => true],
        ],
        'media.visual-grid' => [
            'type' => 'style',
            'path' => '/asset/zoosper-media/css/media-visual-grid.css?v=' . $version,
        ],
    ],
];











