<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

/**
 * Result returned by an admin form processor.
 *
 * Phase 1.41 (page decoupling, part A): relocated to Zoosper\AdminForm —
 * see AdminFormSection.php for the full reasoning. Logic is completely
 * unchanged.
 */
final readonly class AdminFormProcessingResult
{
    /**
     * @param list<string> $errors
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public bool $valid = true,
        public array $errors = [],
        public array $payload = [],
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function success(array $payload = []): self
    {
        return new self(true, [], $payload);
    }

    /** @param list<string> $errors */
    public static function failure(array $errors): self
    {
        return new self(false, $errors, []);
    }

    public function merge(self $other): self
    {
        return new self(
            valid: $this->valid && $other->valid,
            errors: array_values(array_merge($this->errors, $other->errors)),
            payload: array_replace_recursive($this->payload, $other->payload),
        );
    }
}












