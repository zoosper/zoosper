<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

/**
 * Value object for HTML that has passed through the configured sanitizer.
 *
 * The class makes template intent explicit: output that uses this value is not
 * arbitrary raw input, but sanitised CMS body HTML. Do not use this object for
 * OTPs, TOTP setup secrets, recovery-code plaintext, payment data, raw reset
 * tokens, session IDs, SMTP passwords or other secret material.
 */
final readonly class SanitizedHtml
{
    public function __construct(private string $html)
    {
    }

    /**
     * Return the sanitised HTML string for rendering in trusted template slots.
     */
    public function toString(): string
    {
        return $this->html;
    }

    /**
     * Return the sanitised HTML string.
     */
    public function __toString(): string
    {
        return $this->html;
    }
}
