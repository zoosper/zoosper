<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

/**
 * Immutable holder for the current request's resolved site context.
 *
 * Phase 1.34a: this is now a final readonly value holder. The previous version
 * was a mutable, memoised container singleton with a public set() mutator that
 * lazily read $_SERVER - a cross-request/cross-domain state-bleed vector under a
 * resident runtime. It now holds a single immutable SiteContext supplied at
 * construction; it never reads superglobals and cannot be mutated after creation.
 *
 * The authoritative per-request context flows on the Request object
 * (Request::siteContext()). This holder exists only for legacy consumers that
 * still resolve the context through the container; those consumers are migrated to
 * the request-carried context in Phase 1.34b together with the data-model
 * unification and template-layer thread.
 *
 * It contains only public site metadata and must never expose credentials, OTPs,
 * TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment
 * data or customer-private values.
 */
final readonly class CurrentSiteContext
{
    public function __construct(private SiteContext $context)
    {
    }

    /**
     * Return the resolved site context for the current request.
     */
    public function get(): SiteContext
    {
        return $this->context;
    }
}
