<?php

declare(strict_types=1);

return [
    'tables' => [
        'admin_user_two_factor' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'method' => ['type' => 'string', 'length' => 32, 'nullable' => false, 'default' => 'totp'],
                'secret_ciphertext' => ['type' => 'text', 'nullable' => false],
                'enabled_at' => ['type' => 'datetime', 'nullable' => true],
                'last_verified_at' => ['type' => 'datetime', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'idx_admin_user_two_factor_user_method' => ['columns' => ['admin_user_id', 'method'], 'unique' => true],
            ],
        ],
        'admin_user_recovery_codes' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'code_hash' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'used_at' => ['type' => 'datetime', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'idx_admin_user_recovery_codes_user' => ['columns' => ['admin_user_id']],
                'idx_admin_user_recovery_codes_hash' => ['columns' => ['code_hash'], 'unique' => true],
            ],
        ],
        'admin_two_factor_challenges' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'admin_user_id' => ['type' => 'integer', 'nullable' => false],
                'challenge_token_hash' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'expires_at' => ['type' => 'datetime', 'nullable' => false],
                'verified_at' => ['type' => 'datetime', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'idx_admin_two_factor_challenges_user' => ['columns' => ['admin_user_id']],
                'idx_admin_two_factor_challenges_token' => ['columns' => ['challenge_token_hash'], 'unique' => true],
            ],
        ],
    ],
];
