<?php

declare(strict_types=1);

it('ships the Auth-owned Roles collection without expanding ACL data', function (): void {
    $root = dirname(__DIR__, 3);
    $view = (string) file_get_contents($root . '/resources/views/admin/roles/index.latte');
    $script = (string) file_get_contents($root . '/resources/assets/admin/js/roles-workspace.js');
    $css = (string) file_get_contents($root . '/resources/assets/admin/css/roles-workspace.css');
    $grid = (string) file_get_contents($root . '/src/Admin/Grid/RoleGridDefinition.php');
    $read = (string) file_get_contents($root . '/src/Admin/Grid/PdoRoleGridReadRepository.php');
    $assets = require $root . '/config/admin_assets.php';
    $controller = (string) file_get_contents($root . '/src/Admin/Controller/RoleAdminController.php');

    expect($view)->toContain('Users / Roles &amp; Permissions')->toContain('Define reusable permission sets and manage assigned administrators.')->toContain('{$gridHtml|noescape}')->toContain('{else}')
        ->and($script)->toContain("query.placeholder = 'Search roles'")->toContain("link.textContent?.trim() === 'Create role'")->toContain("collection.querySelector('.grid-pagination')")->toContain('form.requestSubmit()')->not->toContain('innerHTML')->not->toContain('fetch(')->not->toContain('localStorage')
        ->and($css)->toContain('Phase 12J-B: Auth-owned Roles and Permissions controlled Grid rollout.')->toContain('body:has([data-roles-index]) .admin-topbar__title')->toContain('font-weight: 400;')->toContain('@media (prefers-contrast:more)')
        ->and($grid)->toContain("KEY = 'admin.roles'")->toContain("GridFilter('q', 'Search')")
        ->and($read)->toContain('SELECT r.id, r.label, r.code')->not->toContain('admin_role_permissions')->not->toContain('admin_user_roles')
        ->and($assets['zoosper-roles-workspace-style']['screens'] ?? [])->toBe(['admin-roles'])
        ->and($assets['zoosper-roles-workspace-runtime']['screens'] ?? [])->toBe(['admin-roles'])
        ->and($controller)->toContain('$gridHtml = $this->gridIndex->render(')->toContain("\$this->renderRoleView('index.php', ['gridHtml' => \$gridHtml])");
});
