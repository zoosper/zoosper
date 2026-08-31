<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Core\Url\AdminUrlGenerator;

/** Thin read-side façade prepared for RoleAdminController::index(). */
final readonly class RoleGridIndex
{
    public function __construct(
        private RoleGridPageBuilder $builder,
        private AuthGridPagePresenter $presenter,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    /** @param array<string, mixed> $queryState */
    public function render(int $authenticatedAdminUserId, array $queryState, ?int $bookmarkId = null): string
    {
        return $this->presenter->present(
            $this->builder->build($authenticatedAdminUserId, $queryState, $bookmarkId),
            $this->adminUrls?->url('roles/create') ?? '/admin/roles/create',
            'Create role',
        );
    }
}










