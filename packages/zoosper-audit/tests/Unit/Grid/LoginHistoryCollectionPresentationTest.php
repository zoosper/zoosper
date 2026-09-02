<?php

declare(strict_types=1);

it('owns the Login History header and canonical email search', function (): void {
    $root = dirname(__DIR__, 3);
    $view = (string) file_get_contents($root . '/resources/views/login-history/index.php');
    $script = (string) file_get_contents($root . '/resources/admin/js/login-history-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/login-history-workspace.css');
    $assets = require $root . '/config/admin_assets.php';

    expect($view)
        ->toContain('login-history-index')
        ->toContain('System / Login History')
        ->toContain('Review authentication activity across Zoosper.')
        ->toContain('$workspaceHtml')
        ->toContain('$gridHtml')
        ->and($script)
        ->toContain("filterForm?.querySelector('[name=\"q\"]')")
        ->toContain("query.placeholder = 'Search email'")
        ->toContain("query.setAttribute('form', filterForm.id)")
        ->toContain('filterForm.requestSubmit()')
        ->not->toContain('localStorage')
        ->not->toContain('fetch(')
        ->and($css)
        ->toContain('Phase 12D: Login History controlled Admin Grid rollout.')
        ->and($assets['assets']['zoosper-login-history-workspace-script']['attributes']['defer'] ?? false)
        ->toBeTrue();
});
