<?php

declare(strict_types=1);

return [
    'tables' => [
        'admin_announcements' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'title' => ['type' => 'string', 'length' => 190, 'nullable' => false],
                'body' => ['type' => 'text', 'nullable' => false],
                'status' => ['type' => 'string', 'length' => 32, 'nullable' => false],
                'published_at' => ['type' => 'datetime', 'nullable' => true],
                'created_by_user_id' => ['type' => 'integer', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_admin_announcements_status' => ['columns' => ['status']],
                'idx_admin_announcements_published' => ['columns' => ['published_at']],
            ],
        ],
        'admin_announcement_acknowledgments' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'announcement_id' => ['type' => 'integer', 'nullable' => false],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'acknowledged_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_announcement_ack_unique' => ['columns' => ['announcement_id', 'admin_user_id'], 'unique' => true],
                'idx_announcement_ack_user' => ['columns' => ['admin_user_id']],
            ],
            'foreign_keys' => [
                'fk_announcement_ack_announcement' => [
                    'columns' => ['announcement_id'],
                    'referenced_table' => 'admin_announcements',
                    'referenced_columns' => ['id'],
                    'on_delete' => 'CASCADE',
                ],
            ],
        ],
    ],
];
