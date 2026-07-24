<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Provides read-only dashboard indicators for Page Admin launch readiness.
 */
final class PageAdminDashboardIndicatorProvider
{
    /**
     * @return list<array{key: string, label: string, status: string, detail: string}>
     */
    public function indicators(): array
    {
        return [
            [
                'key' => 'page_crud_readiness',
                'label' => 'Page CRUD readiness',
                'status' => 'track',
                'detail' => 'Keep page create, edit, list, and status-change readiness visible before launch.',
            ],
            [
                'key' => 'preview_readiness',
                'label' => 'Preview/readiness status',
                'status' => 'track',
                'detail' => 'Keep preview and future frontend rendering work visible beside PageRenderer planning.',
            ],
            [
                'key' => 'sidebar_menu_health',
                'label' => 'Sidebar/menu health',
                'status' => 'track',
                'detail' => 'Track whether admin menu entries are present, duplicate-safe, and linked to valid routes.',
            ],
            [
                'key' => 'route_controller_consistency',
                'label' => 'Route/controller consistency',
                'status' => 'track',
                'detail' => 'Track whether page admin route names, paths, controllers, actions, and permissions remain aligned.',
            ],
            [
                'key' => 'media_dashboard_readiness',
                'label' => 'Media readiness',
                'status' => 'planned',
                'detail' => 'Keep media derivative and upload readiness visible as the page/admin work grows.',
            ],
            [
                'key' => 'documentation_readiness',
                'label' => 'Documentation readiness',
                'status' => 'track',
                'detail' => 'Keep developer-facing docs and rollback notes visible as launch-readiness work continues.',
            ],
        ];
    }
}
