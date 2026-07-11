<?php

declare(strict_types=1);

return [
    'active_format' => 'html',
    'future_format' => 'block_json',
    'supported_formats' => ['html', 'block_json', 'markdown'],
    'block_json' => [
        'schema_version' => 1,
        'allowed_blocks' => ['paragraph', 'header', 'list'],
        'allowed_heading_levels' => [2, 3, 4],
        'max_list_depth' => 3,
    ],
];
