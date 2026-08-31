<?php

declare(strict_types=1);

return [
    /*
     * SMTP configuration for future password reset, 2FA recovery and workflow
     * notifications. Secrets must come from environment variables or a hosting
     * secret store and must never be committed to source control or logs.
     */
    'default' => (string) env('MAIL_TRANSPORT', 'smtp'),
    'from_address' => (string) env('MAIL_FROM_ADDRESS', 'no-reply@example.test'),
    'from_name' => (string) env('MAIL_FROM_NAME', 'Zoosper'),
    'smtp' => [
        'host' => (string) env('SMTP_HOST', '127.0.0.1'),
        'port' => (int) env('SMTP_PORT', 1025),
        'username' => (string) env('SMTP_USERNAME', ''),
        'password' => (string) env('SMTP_PASSWORD', ''),
        'encryption' => (string) env('SMTP_ENCRYPTION', ''),
        'timeout_seconds' => (int) env('SMTP_TIMEOUT_SECONDS', 15),
    ],
];








