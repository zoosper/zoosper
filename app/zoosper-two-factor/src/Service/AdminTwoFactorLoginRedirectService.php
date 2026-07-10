<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Zoosper\Auth\Model\AdminUser;

/**
 * Determines the correct post-login destination for admin users based on 2FA state.
 *
 * If an admin user does not have active 2FA, they should be redirected to the
 * secure setup page. This service does not generate, reveal, log, email or store
 * OTP values, TOTP secrets, QR/provisioning URIs, recovery-code plaintext,
 * reset tokens, SMTP passwords or payment data.
 */
final readonly class AdminTwoFactorLoginRedirectService
{
    public function __construct(
        private AdminTwoFactorEnrollmentService $enrolment,
        private string $adminBasePath = '/admin',
        private string $defaultPath = '/admin',
    ) {
    }

    /**
     * Return the post-login redirect path for an admin user.
     */
    public function pathFor(AdminUser $adminUser): string
    {
        if ($this->enrolment->requiresEnrollment($adminUser->id)) {
            return $this->adminUrl('/2fa/setup');
        }

        return $this->defaultPath;
    }

    /**
     * Return true when the user should be sent to the 2FA setup page.
     */
    public function requiresSetup(AdminUser $adminUser): bool
    {
        return $this->enrolment->requiresEnrollment($adminUser->id);
    }

    /**
     * Build an admin URL from the configured admin base path.
     */
    private function adminUrl(string $path): string
    {
        return rtrim($this->adminBasePath, '/') . '/' . ltrim($path, '/');
    }
}
