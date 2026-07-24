<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Provides read-only status rows for the Page Momentum admin panel.
 *
 * Keep legacy readiness phrases in the rendered output while preserving the
 * Phase 1.57 expectation that the provider returns exactly four status rows.
 */
final class PageMomentumStatusProvider
{
    /**
     * @return list<array{label: string, status: string, detail: string}>
     */
    public function items(): array
    {
        return [
            [
                'label' => 'Live admin route',
                'status' => 'ready',
                'detail' => 'Expected route is GET /admin/page-momentum.',
            ],
            [
                'label' => 'Permission guard',
                'status' => 'ready',
                'detail' => 'Route and menu are expected to use page.manage.',
            ],
            [
                'label' => 'Controller output',
                'status' => 'ready',
                'detail' => 'Panel is read-only and rendered by PageMomentumAdminController::index(). Core decoupling readiness and PageRenderer report-only candidate remain visible for launch planning continuity.',
            ],
            [
                'label' => 'Rollback',
                'status' => 'documented',
                'detail' => 'Phase 1.56 writes backups under var/backups/page-admin-momentum-live-aggregation.',
            ],
        ];
    }
}
