<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Request-scoped access point for the current site/store/store-view context.
 *
 * This wrapper memoises the resolved context for the request so consumers do not
 * need to pass store codes around. It contains only public site metadata and
 * must never expose credentials, OTPs, TOTP secrets, recovery-code plaintext,
 * reset tokens, SMTP passwords, payment data or customer-private values.
 */
final class CurrentSiteContext
{
    private ?SiteContext $context = null;

    public function __construct(private readonly SiteContextResolver $resolver)
    {
    }

    /**
     * Return the current request's site context.
     */
    public function get(): SiteContext
    {
        if ($this->context === null) {
            $this->context = $this->resolver->resolve(
                $_SERVER['HTTP_HOST'] ?? '',
                parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/',
            );
        }

        return $this->context;
    }

    /**
     * Replace the context explicitly for tests or CLI diagnostics.
     */
    public function set(SiteContext $context): void
    {
        $this->context = $context;
    }
}
