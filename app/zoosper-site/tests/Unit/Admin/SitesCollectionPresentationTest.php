<?php
declare(strict_types=1);
it('owns the Sites hierarchy and canonical search', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Admin/Controller/SiteAdminController.php');
    $script = (string) file_get_contents($root . '/resources/admin/js/sites-workspace.js');
    $assets = require $root . '/config/admin_assets.php';
    expect($controller)->toContain('sites-index')->toContain('System / Sites')->toContain('Manage site identities, locales, themes and publication status.')->toContain('Create site')->toContain("'admin.sites'")
        ->and($script)->toContain("filterForm?.querySelector('[name=\"q\"]')")->toContain("query.placeholder = 'Search sites'")->toContain('filterForm.requestSubmit()')->not->toContain('fetch(')->not->toContain('localStorage')
        ->and($assets['assets']['zoosper-sites-workspace-script']['screens'] ?? [])->toBe(['sites'])
        ->and($assets['assets']['zoosper-sites-workspace-script']['attributes']['defer'] ?? false)->toBeTrue();
});
