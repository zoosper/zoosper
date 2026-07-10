<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController;

return [
    /*
     * Admin 2FA setup routes.
     *
     * These routes expose the enrol/re-enrol setup screen introduced in Phase
     * 0.42. They must never log OTPs, TOTP secrets, provisioning URIs, QR data,
     * recovery-code plaintext, reset tokens or SMTP passwords.
     */
    [
        'method' => 'GET',
        'path' => '/admin/2fa/setup',
        'controller' => AdminTwoFactorSetupController::class,
        'action' => 'form',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/2fa/setup',
        'controller' => AdminTwoFactorSetupController::class,
        'action' => 'confirm',
    ],
];
