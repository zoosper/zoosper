<?php

declare(strict_types=1);

return [
    'table' => 'admin_users',
    'columns' => [
        'locale' => [
            'definition' => 'VARCHAR(16) NULL',
            'after' => 'email',
            'comment' => 'Optional admin UI locale preference such as en_AU. NULL uses configured admin locale.',
        ],
    ],
];
