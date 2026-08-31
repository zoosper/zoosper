<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\Core\Url\AdminUrlGenerator;

final readonly class RoleGridWorkspace
{
    public function __construct(
        private RoleGridDefinition $definition,
        private AuthGridWorkspace $workspace,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function action(): string
    {
        return $this->adminUrls?->url('roles') ?? '/admin/roles';
    }

    /**
     * @param array<string, mixed> $queryState
     * @return array{state: GridViewState, html: string}
     */
    public function resolve(int $adminUserId, array $queryState, ?int $bookmarkId = null): array
    {
        return $this->workspace->resolve(
            adminUserId: $adminUserId,
            gridKey: RoleGridDefinition::KEY,
            action: $this->action(),
            definition: $this->definition->build(),
            queryState: $queryState,
            bookmarkId: $bookmarkId,
        );
    }
}










