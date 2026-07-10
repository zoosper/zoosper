<?php

declare(strict_types=1);

namespace Zoosper\Mail\Message;

use InvalidArgumentException;

/**
 * Immutable validated email address value object.
 *
 * A display name can be stored for normal mail headers. Do not use this class to
 * carry passwords, reset tokens, OTPs, recovery codes, or other secrets.
 */
final readonly class EmailAddress
{
    public function __construct(public string $email, public ?string $name = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }
    }

    /**
     * Render a safe RFC-style header value.
     */
    public function headerValue(): string
    {
        if ($this->name === null || trim($this->name) === '') {
            return $this->email;
        }

        $safeName = str_replace(['"', "\r", "\n"], ['', '', ''], $this->name);

        return '"' . $safeName . '" <' . $this->email . '>';
    }
}
