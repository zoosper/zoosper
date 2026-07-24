<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Produces deterministic route/menu registration previews for the page momentum panel.
 *
 * This class does not register anything. It is a final safety bridge before a
 * later phase wires the panel into the real admin router/menu aggregator.
 */
final readonly class PageMomentumAdminIntegrationPreview
{
    public function __construct(
        private PageMomentumRuntimeBridge $bridge = new PageMomentumRuntimeBridge(),
    ) {
    }

    /**
     * @param array<string, mixed> $routeConfig
     * @param array<string, mixed> $menuConfig
     * @return array{wouldRegisterRoutes: list<array<string, mixed>>, wouldRegisterMenuItems: list<array<string, mixed>>, routeCount: int, menuCount: int, liveMutation: bool}
     */
    public function preview(array $routeConfig, array $menuConfig): array
    {
        $definitions = $this->bridge->definitions($routeConfig, $menuConfig);

        return [
            'wouldRegisterRoutes' => $definitions['routes'],
            'wouldRegisterMenuItems' => $definitions['menuItems'],
            'routeCount' => $definitions['routeCount'],
            'menuCount' => $definitions['menuCount'],
            'liveMutation' => false,
        ];
    }
}
