<?php

declare(strict_types=1);

/**
 * Auth module declarative schema.
 *
 * The admin_users base table is created by a file migration; this module-owned
 * schema ADDS the optional admin UI locale column to it. The unified schema
 * engine merges same-named tables and adds only missing columns, so this is a
 * no-op on databases that already have the column.
 */
return [
    'tables' => [
        'admin_users' => [
            'columns' => [
                'locale' => ['type' => 'string', 'length' => 16, 'nullable' => true],
            ],
        ],
    ],
];
