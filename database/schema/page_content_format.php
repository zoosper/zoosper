<?php

declare(strict_types=1);

return [
    'table' => 'pages',
    'columns' => [
        'content_format' => [
            'type' => 'varchar',
            'length' => 32,
            'nullable' => false,
            'default' => 'html',
            'comment' => 'Page body storage format: html, block_json or markdown.',
        ],
        'content_json' => [
            'type' => 'longtext',
            'nullable' => true,
            'comment' => 'Optional structured block JSON document for future block_json pages.',
        ],
    ],
    'safe_defaults' => [
        'content_format' => 'html',
        'content_json' => null,
    ],
];
