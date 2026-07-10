<?php

declare(strict_types=1);

namespace Zoosper\Mail\Message;

use InvalidArgumentException;

/**
 * Immutable outbound email message.
 *
 * Message bodies may later contain sensitive user-specific links. Callers must
 * avoid logging bodies, reset tokens, OTPs, recovery codes or credentials.
 */
final readonly class EmailMessage
{
    /**
     * @param list<EmailAddress> $to
     * @param array<string, string> $headers
     */
    public function __construct(
        public EmailAddress $from,
        public array $to,
        public string $subject,
        public string $textBody,
        public ?string $htmlBody = null,
        public array $headers = [],
    ) {
        if ($to === []) {
            throw new InvalidArgumentException('Email message requires at least one recipient.');
        }

        if (trim($subject) === '') {
            throw new InvalidArgumentException('Email subject cannot be empty.');
        }
    }
}
