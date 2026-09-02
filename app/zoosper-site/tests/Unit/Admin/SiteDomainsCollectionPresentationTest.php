<?php
declare(strict_types=1);
it('owns the Site Domains hierarchy and canonical host search', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Admin/Controller/SiteDomainAdminController.php');
    $script = (string) file_get_contents($root . '/resources/admin/js/site-domains-workspace.js');
    $assets = require $root . '/config/admin_assets.php';
    expect($controller)->toContain('site-domains-index')->toContain('System / Site Domains')->toContain('Manage hostnames and their assigned sites.')->toContain('Create domain')->toContain("'admin.site-domains'")
        ->and($script)->toContain("filterForm?.querySelector('[name=\"q\"]')")->toContain("query.placeholder = 'Search domains'")->toContain('filterForm.requestSubmit()')->not->toContain('fetch(')->not->toContain('localStorage')
        ->and($assets['assets']['zoosper-site-domains-workspace-script']['screens'] ?? [])->toBe(['site-domains'])
        ->and($assets['assets']['zoosper-site-domains-workspace-script']['attributes']['defer'] ?? false)->toBeTrue();
});
