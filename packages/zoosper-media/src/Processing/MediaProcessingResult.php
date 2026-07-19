<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

/**
 * Result contract returned by future media processors.
 */
final readonly class MediaProcessingResult
{
    /**
     * @param array<string, string> $derivatives Map profile code to public path.
     * @param list<string> $errors
     */
    private function __construct(
        public bool $successful,
        public array $derivatives = [],
        public array $errors = [],
    ) {
    }

    /** @param array<string, string> $derivatives */
    public static function success(array $derivatives): self
    {
        return new self(true, $derivatives, []);
    }

    /** @param list<string> $errors */
    public static function failure(array $errors): self
    {
        return new self(false, [], $errors);
    }
}
