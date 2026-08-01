<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

/** Thin read-side façade prepared for UserAdminController::index(). */
final readonly class AdminUserGridIndex
{
    public function __construct(
        private AdminUserGridPageBuilder $builder,
        private AuthGridPagePresenter $presenter,
    ) {
    }

    /** @param array<string, mixed> $queryState */
    public function render(int $authenticatedAdminUserId, array $queryState, ?int $bookmarkId = null): string
    {
        return $this->presenter->present(
            $this->builder->build($authenticatedAdminUserId, $queryState, $bookmarkId),
            '/admin/users/create',
            'Create admin user',
        );
    }
}
