<?php

declare(strict_types=1);

namespace Zoosper\Menu\Lifecycle;

final readonly class MenuLifecycleResult
{
    /** @param array<string,int> $blockers */
    public function __construct(public bool $successful, public string $operation, public int $menuId, public ?string $previousStatus = null, public ?string $newStatus = null, public array $blockers = [], public ?string $message = null) {}
}
