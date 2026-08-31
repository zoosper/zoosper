<?php
declare(strict_types=1);
return [
    'tables' => [
    'config_scope_values' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                // 'default' | 'website' | 'store' | 'site'
                'scope_type' => ['type' => 'string', 'length' => 16, 'nullable' => false],
                // NULL for scope_type='default'. Holds the Site.websiteCode string
                // for 'website', the Site.storeCode string for 'store', or the
                // Site.id (stored as a string) for 'site' — a single flexible
                // column rather than three separate nullable FK columns, since
                // exactly one of those three identifier kinds ever applies to a
                // given row.
                'scope_key' => ['type' => 'string', 'length' => 190, 'nullable' => true],
                // Dot-path config key, e.g. 'general.timezone', 'mail.from_address'.
                'config_path' => ['type' => 'string', 'length' => 190, 'nullable' => false],
                'config_value' => ['type' => 'text', 'nullable' => true],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                // One value per (scope_type, scope_key, config_path) combination.
                'idx_config_scope_values_unique' => [
                    'columns' => ['scope_type', 'scope_key', 'config_path'],
                    'unique' => true,
                ],
                'idx_config_scope_values_path' => ['columns' => ['config_path']],
            ],
        ],
    ],
];











