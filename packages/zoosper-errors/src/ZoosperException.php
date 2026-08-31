<?php

declare(strict_types=1);

namespace Zoosper\Errors;

use Marko\Core\Exceptions\MarkoException;
use Throwable;

/**
 * Developer-friendly framework exception with context and suggested fix.
 *
 * Zoosper follows a loud-errors approach: framework errors explain what
 * failed, where it failed, and what the developer can try next. Error metadata
 * must never include credentials, session IDs, CSRF tokens, OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, SMTP passwords, payment data or
 * customer-private values.
 *
 * Extends Marko's MarkoException to integrate with Marko error reporting
 * while providing additional context, suggestions, docs URL, and details.
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
