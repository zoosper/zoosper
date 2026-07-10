<?php

declare(strict_types=1);

namespace Zoosper\Mail\Message;

use InvalidArgumentException;

/**
 * Immutable validated email address value object.
 *
 * This object may carry a display name for normal email headers. It must not be
 * used to transport credentials, reset tokens, OTPs or recovery-code plaintext.
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
     * Render a safe header value without CR/LF injection.
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
