<?php

declare(strict_types=1);

namespace Zoosper\Errors;

use Marko\Core\Exceptions\MarkoException;
use Throwable;

/**
 * Developer-friendly framework exception with context and suggested fix.
 *
 * Zoosper follows a loud-errors approach: framework errors should explain what
 * failed, where it failed, and what the developer can try next. Error metadata
 * must never include credentials, session IDs, CSRF tokens, OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, SMTP passwords, payment data or
 * customer-private values.
 *
 * PACKAGE EXTRACTION (2026-07-30): moved from Zoosper\Core\Exception (inside
 * app/zoosper-core) to Zoosper\Errors (this standalone zoosper/errors
 * package) — the first module extracted out of zoosper-core into its own
 * package, following the same path-repository pattern already proven by
 * packages/zoosper-media. Exception/error-handling code is a near-leaf
 * dependency (depends on almost nothing else in this codebase, but almost
 * everything else depends on it), making it a clean first extraction
 * candidate. This package's own composer.json requires only marko/core —
 * no other Zoosper module — proof of how cleanly it extracts.
 *
 * FRAMEWORK INTEGRATION (2026-07-30, same day as the extraction): now
 * extends Marko\Core\Exceptions\MarkoException (from the already-installed
 * marko/core package) instead of plain RuntimeException. Rationale, decided
 * explicitly rather than building a competing/richer exception from
 * scratch:
 *
 * - Every real Zoosper feature this class needs (message, context,
 *   suggestion, code, previous) is already present on MarkoException — it
 *   is a strict subset of ZoosperException's shape, not a competing
 *   design. ZoosperException is MarkoException plus two additional fields
 *   (docsUrl, details) that Marko's own base class does not have.
 * - Marko's own error-reporting pipeline (Marko\Errors\ErrorReport::
 *   fromThrowable(), part of the already-installed marko/errors package)
 *   specifically checks `$throwable instanceof MarkoException` before
 *   calling getContext()/getSuggestion() to populate a real error report.
 *   Extending MarkoException means every Zoosper exception is now
 *   automatically recognised there, and by any future Marko package doing
 *   the same check, with zero glue code, forever.
 * - RuntimeException (the previous parent) adds no real behaviour over
 *   plain Exception — it is a semantic marker only. MarkoException itself
 *   extends Exception directly, so this does not lose any meaningful
 *   "runtime exception" semantics.
 *
 * MarkoException declares its own $context/$suggestion as PRIVATE
 * properties with getContext()/getSuggestion() accessors (different
 * method names from this class's own context()/suggestion()) — so this
 * class continues to declare and store its OWN $context/$suggestion (the
 * parent's private properties are inaccessible here regardless), and
 * additionally overrides getContext()/getSuggestion() to return the same
 * values, so Marko's own instanceof-based tooling sees correct data
 * without needing to know about this class's own accessor names at all.
 */
class ZoosperException extends MarkoException
{
    /** @param array<string, mixed> $details */
    public function __construct(
        string $message,
        private readonly string $context = '',
        private readonly string $suggestion = '',
        private readonly ?string $docsUrl = null,
        private readonly array $details = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $suggestion, $code, $previous);
    }

    public function context(): string
    {
        return $this->context;
    }

    public function suggestion(): string
    {
        return $this->suggestion;
    }

    /**
     * Override MarkoException's own getContext(), returning this class's
     * $context — functionally identical to the parent's stored value
     * (both were set from the same constructor argument), declared
     * explicitly so Marko's own tooling (e.g. ErrorReport::fromThrowable())
     * is guaranteed correct regardless of MarkoException's internal
     * implementation details.
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Override MarkoException's own getSuggestion() — see getContext()
     * above for the full reasoning.
     */
    public function getSuggestion(): string
    {
        return $this->suggestion;
    }

    public function docsUrl(): ?string
    {
        return $this->docsUrl;
    }

    /** @return array<string, mixed> */
    public function details(): array
    {
        return $this->details;
    }
}
