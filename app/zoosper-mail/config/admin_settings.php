<?php

declare(strict_types=1);

/**
 * Mail owns this catalogue metadata. Values remain read-only in Admin until the secret-write contract is completed.
 * The Mail runtime resolves Default-scope database overrides in Phase 9F1.
 */
return [[
    'id' => 'mail.delivery',
    'label' => 'Email Delivery',
    'category' => 'communication',
    'description' => 'Outgoing sender identity and SMTP transport configuration.',
    'permission' => 'settings.manage',
    'sort_order' => 100,
    'groups' => [[
        'id' => 'transport',
        'label' => 'Transport',
        'description' => 'Default delivery transport.',
        'sort_order' => 50,
        'open_by_default' => true,
        'settings' => [[
            'path' => 'mail.default', 'label' => 'Default transport', 'type' => 'select',
            'description' => 'Transport used for outgoing messages.', 'default' => 'smtp', 'options' => ['smtp'], 'read_only' => true, 'sort_order' => 100,
        ]],
    ], [
        'id' => 'sender',
        'label' => 'Sender Identity',
        'description' => 'Default identity used for outgoing email.',
        'sort_order' => 100,
        'settings' => [[
            'path' => 'mail.from_address', 'label' => 'From address', 'type' => 'email',
            'description' => 'Default sender email address.', 'default' => 'no-reply@example.test', 'read_only' => true, 'sort_order' => 100,
        ], [
            'path' => 'mail.from_name', 'label' => 'From name', 'type' => 'text',
            'description' => 'Default sender display name.', 'default' => 'Zoosper', 'read_only' => true, 'sort_order' => 200,
        ]],
    ], [
        'id' => 'connection',
        'label' => 'SMTP Connection',
        'description' => 'Connection and transport settings.',
        'sort_order' => 200,
        'settings' => [[
            'path' => 'mail.smtp.host', 'label' => 'Host', 'type' => 'text',
            'description' => 'SMTP server hostname.', 'default' => '127.0.0.1', 'read_only' => true, 'sort_order' => 100,
        ], [
            'path' => 'mail.smtp.port', 'label' => 'Port', 'type' => 'integer',
            'description' => 'SMTP server port.', 'default' => 1025, 'read_only' => true, 'sort_order' => 200,
        ], [
            'path' => 'mail.smtp.encryption', 'label' => 'Encryption', 'type' => 'select',
            'description' => 'SMTP transport encryption.', 'default' => '', 'options' => ['', 'tls', 'ssl'], 'read_only' => true, 'sort_order' => 300,
        ], [
            'path' => 'mail.smtp.timeout_seconds', 'label' => 'Timeout', 'type' => 'integer',
            'description' => 'SMTP connection timeout in seconds.', 'default' => 15, 'read_only' => true, 'sort_order' => 400,
        ]],
    ], [
        'id' => 'authentication',
        'label' => 'Authentication',
        'description' => 'SMTP credentials are redacted and not editable in this phase.',
        'sort_order' => 300,
        'settings' => [[
            'path' => 'mail.smtp.username', 'label' => 'Username', 'type' => 'text',
            'description' => 'SMTP authentication username.', 'default' => '', 'read_only' => true, 'sort_order' => 100,
        ], [
            'path' => 'mail.smtp.password', 'label' => 'Password', 'type' => 'secret',
            'description' => 'SMTP authentication password.', 'secret' => true, 'read_only' => true, 'sort_order' => 200,
        ]],
    ]],
]];
