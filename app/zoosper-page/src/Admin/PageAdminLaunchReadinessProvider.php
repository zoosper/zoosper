<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Provides read-only launch-readiness sections for the Page Admin dashboard.
 */
final class PageAdminLaunchReadinessProvider
{
    /**
     * @return list<array{heading: string, status: string, detail: string}>
     */
    public function sections(): array
    {
        return [
            [
                'heading' => 'Live route and menu',
                'status' => 'active',
                'detail' => 'Expected live admin route is GET /admin/page-momentum with matching menu route admin.page_momentum.index.',
            ],
            [
                'heading' => 'Permission guard',
                'status' => 'active',
                'detail' => 'Route and menu are expected to use page.manage.',
            ],
            [
                'heading' => 'Controller and panel output',
                'status' => 'ready',
                'detail' => 'PageMomentumAdminController::index() renders a read-only panel. Core decoupling readiness and PageRenderer report-only candidate remain visible for launch planning continuity.',
            ],
            [
                'heading' => 'PageRenderer and future content rendering',
                'status' => 'planned',
                'detail' => 'This dashboard keeps PageRenderer work visible while the page/admin launch-readiness arc continues.',
            ],
            [
                'heading' => 'Admin UX readiness',
                'status' => 'in-progress',
                'detail' => 'Next visible work can add richer dashboard checks for page CRUD, preview, sidebar/menu health, and launch readiness.',
            ],
            [
                'heading' => 'Rollback and safety',
                'status' => 'documented',
                'detail' => 'Phase 1.56 writes config backups under var/backups/page-admin-momentum-live-aggregation.',
            ],
        ];
    }
}
