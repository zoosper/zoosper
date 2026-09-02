<?php
declare(strict_types=1);
it('presents authoritative Site Domain fields without changing behaviour', function (): void {
    $root = dirname(__DIR__, 3);
    $grid = (string) file_get_contents($root . '/src/Admin/Grid/SiteDomainGrid.php');
    $script = (string) file_get_contents($root . '/resources/admin/js/site-domains-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/site-domains-workspace.css');
    expect($grid)->toContain('admin.site-domains')->toContain("GridFilter('q', 'Host'")->toContain("GridFilter('primary', 'Primary'")->toContain("'Yes'")->toContain("'No'")
        ->and($script)->toContain("td[data-grid-column=\"host\"]")->toContain("td[data-grid-column=\"site_name\"]")->toContain("td[data-grid-column=\"is_primary\"]")->toContain("footer.dataset.siteDomainsPagination = ''")->toContain("table.insertAdjacentElement('afterend', footer)")->toContain('Node.DOCUMENT_POSITION_FOLLOWING')->toContain('paginationOnly')->not->toContain('innerHTML')->not->toContain('cloneNode')
        ->and($css)->toContain('Phase 12E: Site Domains controlled Admin Grid rollout.')->toContain('.site-domains-index__primary--yes')->toContain(':root[data-admin-theme="dark"]')->toContain(':root[data-admin-theme="ocean"]')->toContain('@media (prefers-contrast: more)');
});
