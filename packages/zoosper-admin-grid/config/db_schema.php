<?php

declare(strict_types=1);

return [
    'tables' => [
        'admin_grid_preferences' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'grid_key' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'visible_columns_json' => ['type' => 'json', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_admin_grid_prefs_user_grid' => ['columns' => ['admin_user_id', 'grid_key'], 'unique' => true],
            ],
            'foreign_keys' => [
                'fk_admin_grid_preferences_user' => ['columns' => ['admin_user_id'], 'referenced_table' => 'admin_users', 'referenced_columns' => ['id'], 'on_delete' => 'CASCADE'],
            ],
        ],
        'admin_grid_bookmarks' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'grid_key' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'name' => ['type' => 'string', 'length' => 120, 'nullable' => false],
                'state_json' => ['type' => 'json', 'nullable' => false],
                'is_default' => ['type' => 'boolean', 'nullable' => false, 'default' => false],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_admin_grid_bookmarks_user_grid_name' => ['columns' => ['admin_user_id', 'grid_key', 'name'], 'unique' => true],
                'idx_admin_grid_bookmarks_default' => ['columns' => ['admin_user_id', 'grid_key', 'is_default']],
            ],
            'foreign_keys' => [
                'fk_admin_grid_bookmarks_user' => ['columns' => ['admin_user_id'], 'referenced_table' => 'admin_users', 'referenced_columns' => ['id'], 'on_delete' => 'CASCADE'],
            ],
        ],
    ],
];











