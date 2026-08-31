<?php

declare(strict_types=1);

namespace Zoosper\Core\Security;

use Zoosper\Core\Http\Application;

final readonly class SecurityHeaders
{
    /**
     * @param array<string, string> $headers Static response headers.
     * @param array<string, mixed>  $csp     Content-Security-Policy config.
     * @param array<string, mixed>  $hsts    Strict-Transport-Security config.
     */
    public function __construct(
        private array $headers,
        private array $csp = [],
        private array $hsts = [],
    ) {
    }

    public function apply(): void
    {
        foreach ($this->resolvedHeaders() as $name => $value) {
            header($name . ': ' . $value);
        }
    }

    /**
     * Compute every header that would be sent, without emitting it. Kept public
     * and side-effect-free so the policy logic (CSP mode, HSTS-on-HTTPS-only) is
     * unit-testable without inspecting real response headers.
     *
     * @return array<string, string>
     */
    public function resolvedHeaders(): array
    {
        $resolved = $this->headers;

        $csp = $this->contentSecurityPolicyHeader();
        if ($csp !== null) {
            [$name, $value] = $csp;
            $resolved[$name] = $value;
        }

        $hsts = $this->strictTransportSecurityValue();
        if ($hsts !== null) {
            $resolved['Strict-Transport-Security'] = $hsts;
        }

        return $resolved;
    }

    /**
     * @return array{0: string, 1: string}|null [headerName, policy] or null when disabled/empty.
     */
    private function contentSecurityPolicyHeader(): ?array
    {
        if (($this->csp['enabled'] ?? false) !== true) {
            return null;
        }

        $policy = trim((string) ($this->csp['policy'] ?? ''));
        if ($policy === '') {
            return null;
        }

        $reportUri = trim((string) ($this->csp['report_uri'] ?? ''));
        if ($reportUri !== '' && !str_contains($policy, 'report-uri')) {
            $policy .= '; report-uri ' . $reportUri;
        }

        $name = ($this->csp['report_only'] ?? false) === true
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        return [$name, $policy];
    }

    /**
     * Build the HSTS header value, or null when disabled or the request is not
     * HTTPS. Reuses the request-scheme detection introduced in Phase 1.100.
     */
    private function strictTransportSecurityValue(): ?string
    {
        if (($this->hsts['enabled'] ?? false) !== true) {
            return null;
        }

        if (!Application::requestIsHttps()) {
            return null;
        }

        $maxAge = max(0, (int) ($this->hsts['max_age'] ?? 31536000));
        $value = 'max-age=' . $maxAge;

        if (($this->hsts['include_subdomains'] ?? false) === true) {
            $value .= '; includeSubDomains';
        }

        if (($this->hsts['preload'] ?? false) === true) {
            $value .= '; preload';
        }

        return $value;
    }
}










