<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Controller\AdminTwoFactorChallengeController;
use Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController;

return [
    /*
     * Admin 2FA setup routes.
     *
     * These routes expose the enrol/re-enrol setup screen. They must never log
     * OTPs, TOTP secrets, provisioning URIs, QR data, recovery-code plaintext,
     * reset tokens or SMTP passwords.
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

    /*
     * Login-time 2FA challenge routes (Phase 1.107).
     *
     * These MUST be public: they are reached while the session is only
     * "pending 2FA" (password verified, second factor not yet supplied), so
     * SessionGuard::user() is still null and the auth guard would otherwise
     * redirect to login. The controller itself enforces that a pending-2FA
     * session exists, and only promotes to authenticated on a valid code.
     */
    [
        'method' => 'GET',
        'path' => '/admin/2fa/challenge',
        'controller' => AdminTwoFactorChallengeController::class,
        'action' => 'form',
        'public' => true,
    ],
    [
        'method' => 'POST',
        'path' => '/admin/2fa/challenge',
        'controller' => AdminTwoFactorChallengeController::class,
        'action' => 'verify',
        'public' => true,
    ],
];










