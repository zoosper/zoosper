<?php

declare(strict_types=1);

namespace Zoosper\Page\Lifecycle;

final readonly class PageLifecycleResult
{
    /** @param array<string,int> $blockers */
    public function __construct(
        public bool $successful,
        public string $operation,
        public int $pageId,
        public ?string $previousStatus = null,
        public ?string $newStatus = null,
        public array $blockers = [],
        public ?string $message = null,
    ) {}
}
