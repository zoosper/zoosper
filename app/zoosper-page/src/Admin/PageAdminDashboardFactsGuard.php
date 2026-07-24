<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Read-only closure guard for the first real dashboard facts.
 */
final class PageAdminDashboardFactsGuard
{
    /**
     * @param list<array{label: string, status: string, detail: string}> $facts
     * @return array<string, mixed>
     */
    public function inspect(array $facts, string $html): array
    {
        $labels = array_map(
            static fn (array $fact): string => $fact['label'],
            $facts,
        );

        $requiredLabels = [
            'Live route fact',
            'Live menu fact',
            'Renderer controller fact',
            'HTTP controller fact',
        ];

        $missingLabels = array_values(array_diff($requiredLabels, $labels));
        $knownStatuses = ['ready', 'track', 'planned', 'active', 'documented', 'in-progress'];
        $unknownStatuses = [];

        foreach ($facts as $fact) {
            if (!in_array($fact['status'], $knownStatuses, true)) {
                $unknownStatuses[] = $fact['status'];
            }
        }

        $checks = [
            'four_facts' => count($facts) === 4,
            'required_fact_labels_present' => $missingLabels === [],
            'known_statuses_only' => $unknownStatuses === [],
            'real_facts_section_visible' => str_contains($html, 'Real dashboard facts'),
            'live_route_fact_visible' => str_contains($html, 'Live route fact'),
            'live_menu_fact_visible' => str_contains($html, 'Live menu fact'),
            'renderer_controller_fact_visible' => str_contains($html, 'Renderer controller fact'),
            'http_controller_fact_visible' => str_contains($html, 'HTTP controller fact'),
            'visual_shell_still_present' => str_contains($html, '<!doctype html>') && str_contains($html, 'zoosper-admin-shell'),
            'status_badges_still_present' => str_contains($html, 'zsp-status'),
            'read_only_still_present' => str_contains($html, 'read-only'),
        ];

        return [
            'checks' => $checks,
            'missingLabels' => $missingLabels,
            'unknownStatuses' => array_values(array_unique($unknownStatuses)),
            'factCount' => count($facts),
            'ok' => !in_array(false, $checks, true),
        ];
    }
}
