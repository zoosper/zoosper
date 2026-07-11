<?php

declare(strict_types=1);

namespace Zoosper\Admin\Message;

/**
 * Immutable admin flash/toast message value object.
 *
 * Flash messages are for short admin UI feedback only. They must never contain
 * OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords,
 * payment data, session IDs, raw exception traces or customer-private values.
 */
final readonly class FlashMessage
{
    public const SUCCESS = 'success';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';

    public function __construct(
        public string $type,
        public string $text,
        public string $key,
        public bool $dismissible = true,
    ) {
    }

    /**
     * Create a message from an array stored in the admin session.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: self::normaliseType((string) ($data['type'] ?? self::INFO)),
            text: (string) ($data['text'] ?? ''),
            key: (string) ($data['key'] ?? 'message'),
            dismissible: (bool) ($data['dismissible'] ?? true),
        );
    }

    /**
     * Convert the message to session-storable data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => self::normaliseType($this->type),
            'text' => $this->text,
            'key' => $this->key,
            'dismissible' => $this->dismissible,
        ];
    }

    /**
     * Restrict message type to the known UI variants.
     */
    public static function normaliseType(string $type): string
    {
        return in_array($type, [self::SUCCESS, self::ERROR, self::WARNING, self::INFO], true)
            ? $type
            : self::INFO;
    }
}
