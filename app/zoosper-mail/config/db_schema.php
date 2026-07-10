<?php

declare(strict_types=1);

return [
    'tables' => [
        'smtp_email_log' => [
            'columns' => [
                'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'message_uuid' => ['type' => 'string', 'length' => 64, 'nullable' => false],
                'transport' => ['type' => 'string', 'length' => 32, 'nullable' => false, 'default' => 'smtp'],
                'status' => ['type' => 'string', 'length' => 32, 'nullable' => false, 'default' => 'pending'],
                'from_email' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                'from_name' => ['type' => 'string', 'length' => 255, 'nullable' => true],
                'to_emails' => ['type' => 'text', 'nullable' => false],
                'subject' => ['type' => 'string', 'length' => 255, 'nullable' => false],

                /*
                 * The current declarative schema engine supports `text` but not
                 * `longtext`. Keep bodies as text for now so migrations run
                 * cleanly. A future schema-engine phase can add native longtext
                 * support if larger message retention becomes necessary.
                 *
                 * PCI-aware rule: message bodies must not contain OTPs, TOTP
                 * secrets, recovery-code plaintext, provisioning URIs, reset
                 * tokens, SMTP passwords, payment data or other sensitive
                 * values unless a future masking policy protects them before
                 * storage.
                 */
                'text_body' => ['type' => 'text', 'nullable' => true],
                'html_body' => ['type' => 'text', 'nullable' => true],

                'error_class' => ['type' => 'string', 'length' => 255, 'nullable' => true],
                'error_message' => ['type' => 'text', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
                'sent_at' => ['type' => 'datetime', 'nullable' => true],
                'failed_at' => ['type' => 'datetime', 'nullable' => true],
            ],
            'indexes' => [
                'idx_smtp_email_log_uuid' => ['columns' => ['message_uuid'], 'unique' => true],
                'idx_smtp_email_log_status' => ['columns' => ['status']],
                'idx_smtp_email_log_created' => ['columns' => ['created_at']],
                'idx_smtp_email_log_from' => ['columns' => ['from_email']],
            ],
        ],
    ],
];
