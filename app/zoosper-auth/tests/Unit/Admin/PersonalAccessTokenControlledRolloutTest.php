<?php

declare(strict_types=1);

it('connects the owner-scoped token Grid without weakening credential boundaries', function (): void {
    $root = dirname(__DIR__, 3);
    $view = (string) file_get_contents($root . '/resources/views/admin/access-tokens/index.latte');
    $css = (string) file_get_contents($root . '/resources/assets/admin/css/personal-access-tokens.css');
    $controller = (string) file_get_contents($root . '/src/Admin/Controller/PersonalAccessTokenAdminController.php');
    $grid = (string) file_get_contents($root . '/src/Admin/Grid/AccessToken/AccessTokenGrid.php');

    expect($view)->toContain('Users · Security')
        ->toContain('Your tokens')
        ->toContain('{$gridHtml|noescape}')
        ->toContain('cannot be shown again')
        ->not->toContain('tokenHash')
        ->not->toContain('token_hash')
        ->and($css)->toContain('Phase 12H: controlled Access Tokens collection integration.')
        ->toContain('font-weight: 400;')
        ->toContain('.pat-grid-search')
        ->toContain('.pat-token-list [data-grid-export]')
        ->toContain('@media (max-width: 48rem)')
        ->toContain('@media (prefers-contrast: more)')
        ->and($controller)->toContain('AccessTokenGrid::KEY')
        ->toContain('allForUser($user->id)')
        ->toContain('revoke($id, $user->id')
        ->not->toContain("'token_hash'")
        ->and($grid)->toContain("KEY='admin.access-tokens'")
        ->toContain("'admin_user_id=:owner'")
        ->toContain("GridFilter('status', 'Status'")
        ->toContain("'active'")
        ->toContain("'expired'")
        ->toContain("'revoked'");
});
