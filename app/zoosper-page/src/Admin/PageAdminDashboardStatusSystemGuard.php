<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only closure guard for the Page Admin dashboard visual status system.
 */
final class PageAdminDashboardStatusSystemGuard
{
    /**
     * @return array<string, mixed>
     */
    public function inspect(string $html): array
    {
        $requiredTokens = [
            'zsp-status',
            'zsp-status--ready',
            'zsp-status--active',
            'zsp-status--track',
            'zsp-status--planned',
            'zsp-status--documented',
            'zsp-status--in-progress',
        ];

        $missingTokens = [];
        foreach ($requiredTokens as $token) {
            if (!str_contains($html, $token)) {
                $missingTokens[] = $token;
            }
        }

        $checks = [
            'standalone_shell_present' => str_contains($html, '<!doctype html>') && str_contains($html, 'zoosper-admin-shell'),
            'card_grid_present' => str_contains($html, 'zoosper-admin-card') && str_contains($html, 'zoosper-admin-grid'),
            'status_tokens_present' => $missingTokens === [],
            'dashboard_title_present' => str_contains($html, 'Page Admin launch-readiness dashboard'),
            'dashboard_indicators_present' => str_contains($html, 'Dashboard indicators'),
            'route_present' => str_contains($html, '/admin/page-momentum'),
            'permission_present' => str_contains($html, 'page.manage'),
            'read_only_present' => str_contains($html, 'read-only'),
        ];

        return [
            'checks' => $checks,
            'missingTokens' => $missingTokens,
            'statusTokenCount' => count($requiredTokens),
            'ok' => !in_array(false, $checks, true),
        ];
    }
}
