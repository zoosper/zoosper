<?php

declare(strict_types=1);

namespace Zoosper\Page\Application\Save;

final readonly class PageSaveResult
{
    private function __construct(
        public bool $successful,
        public ?int $pageId = null,
        public ?string $error = null,
        public bool $processorRejected = false,
    ) {
    }

    public static function success(int $pageId): self { return new self(true, $pageId); }
    public static function failure(string $error, bool $processorRejected = false): self
    {
        return new self(false, error: $error, processorRejected: $processorRejected);
    }
}










