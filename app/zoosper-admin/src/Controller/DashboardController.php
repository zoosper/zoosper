<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Access\RoleProviderInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class DashboardController
{
    public function __construct(private RoleProviderInterface $roles)
    {
    }

    public function index(Request $request): Response
    {
        $role = $this->roles->get('super_admin');
        $canAccessAdmin = $role?->allows(Permission::AdminAccess) ?? false;

        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Zoosper Admin</title>'
            . '<style>body{font-family:system-ui;margin:3rem;line-height:1.5;color:#102a43} .card{max-width:760px;padding:2rem;border:1px solid #d9e2ec;border-radius:16px;background:#f8fbfb}</style>'
            . '</head><body><main class="card">'
            . '<h1>Zoosper Admin</h1>'
            . '<p>Hello from the admin.</p>'
            . '<p><strong>Role placeholder:</strong> ' . htmlspecialchars($role?->label ?? 'none', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Admin access:</strong> ' . ($canAccessAdmin ? 'allowed' : 'denied') . '</p>'
            . '<p>Next phase: real authentication, sessions, database roles and permissions.</p>'
            . '</main></body></html>';

        return Response::html($html);
    }
}
