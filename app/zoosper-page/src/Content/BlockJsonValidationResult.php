<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Validation result for Editor.js-style block JSON documents.
 */
final readonly class BlockJsonValidationResult
{
    /** @param list<string> $errors */
    public function __construct(public bool $valid, public array $errors = [])
    {
    }

    public static function ok(): self
    {
        return new self(true);
    }

    /** @param list<string> $errors */
    public static function fail(array $errors): self
    {
        return new self(false, $errors);
    }
}
