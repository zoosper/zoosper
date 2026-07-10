<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

/**
 * Builds otpauth:// provisioning URIs for admin TOTP enrolment.
 *
 * Provisioning URIs contain the TOTP secret. They must only be shown during the
 * enrolment flow and must never be logged or stored in audit records.
 */
final readonly class TotpProvisioningUriGenerator
{
    public function __construct(
        private string $issuer = 'Zoosper',
        private int $digits = 6,
        private int $period = 30,
        private string $algorithm = 'SHA1',
    ) {
    }

    /**
     * Build a provisioning URI for an admin account label.
     */
    public function generate(string $accountLabel, string $secret): string
    {
        $label = rawurlencode($this->issuer . ':' . $accountLabel);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $this->issuer,
            'algorithm' => $this->algorithm,
            'digits' => $this->digits,
            'period' => $this->period,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'otpauth://totp/' . $label . '?' . $query;
    }
}
