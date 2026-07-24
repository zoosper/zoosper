<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Presents dashboard statuses as stable visual CSS tokens.
 */
final class PageAdminDashboardStatusPresenter
{
    /**
     * @return non-empty-string
     */
    public function classFor(string $status): string
    {
        $normalised = strtolower(trim($status));

        return match ($normalised) {
            'ready' => 'zsp-status zsp-status--ready',
            'active' => 'zsp-status zsp-status--active',
            'track' => 'zsp-status zsp-status--track',
            'planned' => 'zsp-status zsp-status--planned',
            'documented' => 'zsp-status zsp-status--documented',
            'in-progress' => 'zsp-status zsp-status--in-progress',
            default => 'zsp-status zsp-status--neutral',
        };
    }
}
