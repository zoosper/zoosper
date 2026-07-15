<?php

declare(strict_types=1);

return [
    'tables' => [
        'schema_snapshots' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'schema_hash' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'statements_json' => ['type' => 'json', 'nullable' => false],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_schema_snapshots_hash' => ['columns' => ['schema_hash']],
                'idx_schema_snapshots_created' => ['columns' => ['created_at']],
            ],
        ],
    ],
];
