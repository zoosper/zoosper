<?php

declare(strict_types=1);

it('ships Auth-owned Fable security workspaces without weakening mutations', function (): void {
    $module = dirname(__DIR__, 3);
    $project = dirname($module, 2);
    $assets = require $module . '/config/admin_assets.php';
    $userView = (string) file_get_contents($module . '/resources/views/admin/users/form.latte');
    $userCss = (string) file_get_contents($module . '/resources/assets/admin/css/admin-user-workspace.css');
    $publicUserCss = (string) file_get_contents($project . '/public/assets/admin/css/admin-user-workspace.css');
    $permissionJs = (string) file_get_contents($module . '/resources/assets/admin/js/permission-explorer.js');

    expect($assets)->toHaveKey('zoosper-admin-user-workspace-style')
        ->and($assets['zoosper-admin-user-workspace-style']['screens'])->toBe(['admin-users'])
        ->and($userCss)->toBe($publicUserCss)
        ->toContain('grid-template-columns: minmax(0,1.35fr) minmax(18rem,.65fr)')
        ->toContain('@media (max-width:42rem)')
        ->toContain('@media (prefers-reduced-motion:reduce)')
        ->and($userView)->toContain('class="admin-user-workspace"')
        ->toContain('method="post"')
        ->toContain('name="_csrf_token"')
        ->toContain('name="role_ids[]"')
        ->toContain('name="_action" value="reset_2fa"')
        ->not->toMatch('/<(?:script|style)\b/i')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->and($permissionJs)->not->toContain('innerHTML')
        ->not->toContain('insertAdjacentHTML')
        ->not->toContain('fetch(')
        ->not->toContain('.submit(');
});










