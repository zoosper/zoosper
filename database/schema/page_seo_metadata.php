<?php

declare(strict_types=1);

return [
    'table' => 'pages',
    'columns' => [
        'meta_title' => [
            'type' => 'varchar',
            'length' => 255,
            'nullable' => true,
            'comment' => 'Optional SEO title override for the page.',
        ],
        'meta_description' => [
            'type' => 'varchar',
            'length' => 500,
            'nullable' => true,
            'comment' => 'Optional SEO meta description for the page.',
        ],
        'meta_keywords' => [
            'type' => 'varchar',
            'length' => 500,
            'nullable' => true,
            'comment' => 'Optional legacy SEO keywords field.',
        ],
        'canonical_url' => [
            'type' => 'varchar',
            'length' => 500,
            'nullable' => true,
            'comment' => 'Optional canonical URL for the page.',
        ],
    ],
];
