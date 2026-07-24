<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only invariant guard for the Page Admin launch-readiness dashboard.
 */
final class PageAdminLaunchReadinessDashboardGuard
{
    /**
     * @param list<array{heading: string, status: string, detail: string}> $sections
     * @param string $html
     * @return array<string, mixed>
     */
    public function inspect(array $sections, string $html): array
    {
        $headings = array_map(
            static fn (array $section): string => $section['heading'],
            $sections,
        );

        $requiredHeadings = [
            'Live route and menu',
            'Permission guard',
            'Controller and panel output',
            'PageRenderer and future content rendering',
            'Admin UX readiness',
            'Rollback and safety',
        ];

        $missingHeadings = array_values(array_diff($requiredHeadings, $headings));
        $checks = [
            'six_sections' => count($sections) === 6,
            'required_headings_present' => $missingHeadings === [],
            'contains_dashboard_title' => str_contains($html, 'Page Admin launch-readiness dashboard'),
            'contains_route' => str_contains($html, '/admin/page-momentum'),
            'contains_permission' => str_contains($html, 'page.manage'),
            'contains_read_only' => str_contains($html, 'read-only'),
            'contains_page_renderer_candidate' => str_contains($html, 'PageRenderer report-only candidate'),
            'contains_core_decoupling_readiness' => str_contains($html, 'Core decoupling readiness'),
        ];

        return [
            'checks' => $checks,
            'missingHeadings' => $missingHeadings,
            'sectionCount' => count($sections),
            'ok' => !in_array(false, $checks, true),
        ];
    }
}
