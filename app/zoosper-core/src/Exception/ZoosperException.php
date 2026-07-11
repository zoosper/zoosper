<?php

declare(strict_types=1);

namespace Zoosper\Core\Exception;

use RuntimeException;
use Throwable;

/**
 * Developer-friendly framework exception with context and suggested fix.
 *
 * Zoosper follows a loud-errors approach: framework errors should explain what
 * failed, where it failed, and what the developer can try next. Error metadata
 * must never include credentials, session IDs, CSRF tokens, OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, SMTP passwords, payment data or
 * customer-private values.
 */
class ZoosperException extends RuntimeException
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
        parent::__construct($message, $code, $previous);
    }

    public function context(): string
    {
        return $this->context;
    }

    public function suggestion(): string
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
