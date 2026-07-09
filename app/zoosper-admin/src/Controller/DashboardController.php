<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class DashboardController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->requirePermission(Permission::AdminAccess->value);
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $token = htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8');
        $permissions = htmlspecialchars(implode(', ', $user->permissions), ENT_QUOTES, 'UTF-8');
        $content = <<<HTML
<div class="cards">
    <section class="card"><h2>Pages</h2><p>Create, edit, preview, publish and unpublish CMS pages.</p><a class="button" href="/admin/pages">Manage pages</a></section>
    <section class="card"><h2>Sites</h2><p>Site/domain management is planned next.</p><button type="button" class="secondary" disabled>Coming soon</button></section>
    <section class="card"><h2>Your permissions</h2><p class="muted">{$permissions}</p></section>
</div>
<form method="post" action="/admin/logout" class="toolbar"><input type="hidden" name="_csrf_token" value="{$token}"><button class="secondary">Log out</button></form>
HTML;

        return Response::html($this->layout->render('Dashboard', $content, $user, 'dashboard'));
    }
}
