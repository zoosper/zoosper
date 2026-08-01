<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\AdminGrid\GridViewState;

final readonly class AdminUserGridWorkspace
{
    public const ACTION = '/admin/users';

    public function __construct(
        private AdminUserGridDefinition $definition,
        private AuthGridWorkspace $workspace,
    ) {
    }

    /**
     * @param array<string, mixed> $queryState
     * @return array{state: GridViewState, html: string}
     */
    public function resolve(int $adminUserId, array $queryState, ?int $bookmarkId = null): array
    {
        return $this->workspace->resolve(
            adminUserId: $adminUserId,
            gridKey: AdminUserGridDefinition::KEY,
            action: self::ACTION,
            definition: $this->definition->build(),
            queryState: $queryState,
            bookmarkId: $bookmarkId,
        );
    }
}
