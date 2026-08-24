<?php

declare(strict_types=1);

$stylesheet = dirname(__DIR__) . '/resources/admin/css/media-visual-grid.css';
$version = substr(hash_file('sha256', $stylesheet) ?: 'dev', 0, 12);

return [
    'assets' => [
        'media.visual-grid' => [
            'type' => 'style',
            'path' => '/asset/zoosper-media/css/media-visual-grid.css?v=' . $version,
        ],
    ],
];
