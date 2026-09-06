<?php

declare(strict_types=1);

namespace Zoosper\Auth\PasswordReset;

use InvalidArgumentException;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Builds reset links from one explicitly configured HTTP(S) application origin. */
final readonly class AdminPasswordResetUrlBuilder
{
    public function __construct(private string $applicationUrl, private AdminUrlGenerator $adminUrls)
    {
    }

    public function build(string $token): string
    {
        $origin = rtrim(trim($this->applicationUrl), '/');
        $parts = parse_url($origin);
        if (!is_array($parts)
            || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || trim((string) ($parts['host'] ?? '')) === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])) {
            throw new InvalidArgumentException('APP_URL must be an absolute HTTP or HTTPS origin without credentials, query or fragment.');
        }

        return $origin . $this->adminUrls->url('reset-password', ['token' => $token]);
    }
}
