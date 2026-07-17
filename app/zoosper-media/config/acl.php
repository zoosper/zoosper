<?php

declare(strict_types=1);

return [
    'groups' => [
        'media' => [
            'label' => 'Media',
            'sort_order' => 40,
        ],
    ],
    'permissions' => [
        'media.manage' => [
            'label' => 'Manage media assets',
            'parent_code' => 'media',
            'sort_order' => 10,
        ],
    ],
];
