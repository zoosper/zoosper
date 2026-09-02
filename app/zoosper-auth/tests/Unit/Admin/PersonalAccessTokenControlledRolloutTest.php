<?php
declare(strict_types=1);
it('connects the owner-scoped token Grid without weakening credential boundaries', function (): void {
    $root = dirname(__DIR__, 3);
    $view = (string) file_get_contents($root . '/resources/views/admin/access-tokens/index.latte');
    $script = (string) file_get_contents($root . '/resources/assets/admin/js/personal-access-tokens.js');
    $css = (string) file_get_contents($root . '/resources/assets/admin/css/personal-access-tokens.css');
    $controller = (string) file_get_contents($root . '/src/Admin/Controller/PersonalAccessTokenAdminController.php');
    $grid = (string) file_get_contents($root . '/src/Admin/Grid/AccessToken/AccessTokenGrid.php');
    expect($view)->toContain('Users · Security')->toContain('Your tokens')->toContain('{$gridHtml|noescape}')->toContain('cannot be shown again')->not->toContain('tokenHash')->not->toContain('token_hash')
        ->and($script)->toContain("document.querySelector('.admin-topbar__title')")->toContain("shellTitle?.textContent?.trim() === 'Personal Access Tokens'")->toContain('shellTitle.hidden = true')->toContain("query.placeholder = 'Search tokens'")->toContain("pagination.dataset.patPagination = ''")->toContain("scroll?.querySelector('.grid-pagination')")->toContain("workspace.querySelectorAll('[data-grid-export]')")->toContain("control.hidden = true")->toContain('form.requestSubmit()')->not->toContain('innerHTML')->not->toContain('fetch(')->not->toContain('localStorage')
        ->and($css)->toContain('Phase 12H: controlled Access Tokens collection integration.')->toContain('Phase 12H-B: PAT-only capability and compact desktop pagination correction.')->toContain('Phase 12H-C: use the native Grid pagination DOM and regular Grid text weight.')->toContain('font-weight: 400;')->toContain('.pat-grid-search')->toContain('.pat-token-list [data-grid-export]')->toContain('display: flex !important;')->toContain('flex-flow: row nowrap;')->toContain('@media (max-width: 48rem)')->toContain('flex-flow: row wrap;')->toContain('@media (prefers-contrast: more)')
        ->and($controller)->toContain('AccessTokenGrid::KEY')->toContain('allForUser($user->id)')->toContain('revoke($id, $user->id')->not->toContain("'token_hash'")
        ->and($grid)->toContain("KEY='admin.access-tokens'")->toContain("'admin_user_id=:owner'")->toContain("GridFilter('status', 'Status'")->toContain("'active'")->toContain("'expired'")->toContain("'revoked'");
});
