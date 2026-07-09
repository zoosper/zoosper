<?php

declare(strict_types=1);

return [
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
];
