<?php
declare(strict_types=1);
return [
    'tables' => [
        'admin_dashboard_preferences' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'hidden_widget_codes_json' => ['type' => 'json', 'nullable' => false],
                'widget_order_json' => ['type' => 'json', 'nullable' => false],
                'updated_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_admin_dashboard_preferences_user' => ['columns' => ['admin_user_id'], 'unique' => true],
            ],
        ],
        'admin_login_history' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => true],
                'email' => ['type' => 'string', 'length' => 190, 'nullable' => false],
                'status' => ['type' => 'string', 'length' => 32, 'nullable' => false],
                'ip_address' => ['type' => 'string', 'length' => 64, 'nullable' => true],
                'user_agent' => ['type' => 'text', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_admin_login_history_user' => ['columns' => ['admin_user_id']],
                'idx_admin_login_history_email' => ['columns' => ['email']],
                'idx_admin_login_history_status' => ['columns' => ['status']],
                'idx_admin_login_history_created' => ['columns' => ['created_at']],
            ],
        ],
        'admin_activity_log' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => true],
                'actor_email' => ['type' => 'string', 'length' => 190, 'nullable' => true],
                'action' => ['type' => 'string', 'length' => 120, 'nullable' => false],
                'entity_type' => ['type' => 'string', 'length' => 120, 'nullable' => false],
                'entity_id' => ['type' => 'string', 'length' => 120, 'nullable' => true],
                'summary' => ['type' => 'text', 'nullable' => false],
                'metadata_json' => ['type' => 'json', 'nullable' => true],
                'ip_address' => ['type' => 'string', 'length' => 64, 'nullable' => true],
                'user_agent' => ['type' => 'text', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false],
            ],
            'indexes' => [
                'idx_admin_activity_user' => ['columns' => ['admin_user_id']],
                'idx_admin_activity_action' => ['columns' => ['action']],
                'idx_admin_activity_entity' => ['columns' => ['entity_type', 'entity_id']],
                'idx_admin_activity_created' => ['columns' => ['created_at']],
            ],
        ],
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
        ],
    ],
];

