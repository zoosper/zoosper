<?php
declare(strict_types=1);
it('ships the Auth-owned Admin Users controlled Grid without expanding identity data', function (): void {
    $root = dirname(__DIR__, 3);
    $view = (string) file_get_contents($root . '/resources/views/admin/users/index.latte');
    $script = (string) file_get_contents($root . '/resources/assets/admin/js/admin-users-workspace.js');
    $css = (string) file_get_contents($root . '/resources/assets/admin/css/admin-user-workspace.css');
    $grid = (string) file_get_contents($root . '/src/Admin/Grid/AdminUserGridDefinition.php');
    $read = (string) file_get_contents($root . '/src/Admin/Grid/PdoAdminUserGridReadRepository.php');
    $assets = require $root . '/config/admin_assets.php';
    expect($view)->toContain('Users / Admin Users')->toContain('Manage administrator identities, access status and account settings.')->toContain('{$gridHtml|noescape}')->toContain('{else}')
        ->and($script)->toContain("query.placeholder = 'Search users'")->not->toContain('.admin-topbar__title')->toContain("link.textContent?.trim() === 'Create admin user'")->toContain("collection.querySelector('.grid-pagination')")->toContain('form.requestSubmit()')->not->toContain('innerHTML')->not->toContain('fetch(')->not->toContain('localStorage')
        ->and($css)->toContain('Phase 12I: Auth-owned Admin Users controlled Grid rollout.')->not->toContain('body:has([data-admin-users-index]) .admin-topbar__title')->toContain('font-weight: 400;')->toContain('.admin-users-index__status--active')->toContain('.admin-users-index__status--inactive')->toContain('@media (prefers-contrast: more)')
        ->and($grid)->toContain("KEY = 'admin.users'")->toContain("GridFilter('q', 'Search')")->toContain("'active'")->toContain("'inactive'")
        ->and($read)->toContain('SELECT u.id, u.name, u.email, u.status')->not->toContain('password_hash')->not->toContain('two_factor_secret')->not->toContain('recovery_codes')
        ->and($assets['zoosper-admin-users-workspace-runtime']['screens'] ?? [])->toBe(['admin-users']);
});

it('uses the backwards-compatible server-owned shell title policy', function (): void {
    $root = dirname(__DIR__, 4);
    $layout = (string) file_get_contents($root . '/zoosper-admin/src/Layout/AdminLayout.php');
    $template = (string) file_get_contents(dirname($root) . '/themes/admin/default/templates/layout.php');
    $controller = (string) file_get_contents($root . '/zoosper-auth/src/Admin/Controller/UserAdminController.php');
    expect($layout)
        ->toContain('?string $shellTitle = null')
        ->toContain("'shellTitle' => " . '$shellTitle ?? $title')
        ->and($template)
        ->toContain('($shellTitle ?? $title) !== ' . "''")
        ->toContain('$e($shellTitle ?? $title)')
        ->and($controller)
        ->toContain("shellTitle: ''");
});
