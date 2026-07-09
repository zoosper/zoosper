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
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->requirePermission(Permission::AdminAccess->value);

        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $name = htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8');
        $permissions = htmlspecialchars(implode(', ', $user->permissions), ENT_QUOTES, 'UTF-8');
        $token = htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8');

        return Response::html(<<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zoosper Admin</title>
    <style>
        body { font-family: system-ui; margin: 3rem; color: #102a43; }
        .card { max-width: 860px; padding: 2rem; border: 1px solid #d9e2ec; border-radius: 16px; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Zoosper Admin</h1>
        <p>Hello, <strong>{$name}</strong>.</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Permissions:</strong> {$permissions}</p>
        <p>Phase 0.3 adds site/domain resolution and published page rendering.</p>
        <form method="post" action="/admin/logout">
            <input type="hidden" name="_csrf_token" value="{$token}">
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
HTML);
    }
}
