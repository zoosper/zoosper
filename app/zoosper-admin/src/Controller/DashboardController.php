<?php
declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class DashboardController
{
    public function __construct(private SessionGuard $guard, private CsrfTokenManager $csrf)
    {
    }

    public function index(Request $r): Response
    {
        $u = $this->guard->requirePermission(Permission::AdminAccess->value);
        if ($u === null) return Response::redirect('/admin/login');
        $perms = htmlspecialchars(implode(', ', $u->permissions), ENT_QUOTES, 'UTF-8');
        $t = htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8');
        return Response::html('<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Zoosper Admin</title><style>body{font-family:system-ui;margin:3rem}.card{max-width:820px;padding:2rem;border:1px solid #ddd;border-radius:16px}</style></head><body><main class="card"><h1>Zoosper Admin</h1><p>Hello, <strong>' . htmlspecialchars($u->name, ENT_QUOTES, 'UTF-8') . '</strong>.</p><p>Persistent login, roles and permissions are working.</p><p>' . $perms . '</p><form method="post" action="/admin/logout"><input type="hidden" name="_csrf_token" value="' . $t . '"><button>Log out</button></form></main></body></html>');
    }
}
