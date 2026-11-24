<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\AdminForm\AdminFormDefinition;
use Zoosper\AdminForm\AdminFormField;
use Zoosper\AdminForm\AdminFormRegistry;
use Zoosper\AdminForm\AdminFormRenderer;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Site\Admin\Controller\SiteAdminController;
use Zoosper\Site\Repository\SiteRepository;

it('renders registry form errors with the existing truthiness and escaping behavior', function (?string $error, ?string $alert): void {
    $pdo = new PDO('sqlite::memory:');
    $registry = new AdminFormRegistry();
    $registry->register(new AdminFormDefinition('admin.sites.form', [
        new AdminFormField('name', 'text', 'Name', 10),
    ]));

    $controller = new SiteAdminController(
        guard: new SessionGuard(new AdminUserRepository($pdo)),
        csrf: new CsrfTokenManager(),
        sites: new SiteRepository($pdo),
        layout: new AdminLayout(new AdminMenu(new AdminMenuLoader(new ModuleRegistry(dirname(__DIR__, 5))))),
        formRegistry: $registry,
        formRenderer: new AdminFormRenderer(),
    );

    $session = $_SESSION ?? null;
    try {
        $html = (new ReflectionMethod(SiteAdminController::class, 'form'))->invoke(
            $controller, '/admin/sites/create', null, $error, ['name' => 'Example site']
        );
    } finally {
        if ($session === null) {
            unset($_SESSION);
        } else {
            $_SESSION = $session;
        }
    }

    expect($html)->toContain('<form action="/admin/sites/create" method="POST" class="admin-form">')
        ->toContain('name="name" value="Example site"')
        ->toContain('name="_csrf_token"');

    if ($alert === null) {
        expect($html)->not->toContain('admin-alert--danger');
    } else {
        expect($html)->toContain('<div class="admin-alert admin-alert--danger">' . $alert . '</div>');
        if ($error !== $alert) {
            expect($html)->not->toContain($error);
        }
    }
})->with([
    'null' => [null, null],
    'empty' => ['', null],
    'zero' => ['0', null],
    'ordinary' => ['Site name is required.', 'Site name is required.'],
    'HTML' => ['<script>alert("x & y")</script>', '&lt;script&gt;alert(&quot;x &amp; y&quot;)&lt;/script&gt;'],
]);