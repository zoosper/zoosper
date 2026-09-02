<?php
declare(strict_types=1);
it('presents authoritative Site fields without changing behaviour', function (): void {
    $root = dirname(__DIR__, 3);
    $grid = (string) file_get_contents($root . '/src/Admin/Grid/SiteGrid.php');
    $script = (string) file_get_contents($root . '/resources/admin/js/sites-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/sites-workspace.css');
    expect($grid)->toContain("KEY='admin.sites'")->toContain("GridFilter('q', 'Search'")->toContain("GridFilter('status', 'Status'")->toContain("'active'")->toContain("'inactive'")
        ->and($script)->toContain('td[data-grid-column="name"]')->toContain('td[data-grid-column="code"]')->toContain('td[data-grid-column="status"]')->toContain('td[data-grid-column="locale"]')->toContain('td[data-grid-column="theme_code"]')->toContain("footer.dataset.sitesPagination = ''")->toContain('Node.DOCUMENT_POSITION_FOLLOWING')->not->toContain('innerHTML')->not->toContain('cloneNode')
        ->and($css)->toContain('Phase 12F: Sites controlled Admin Grid rollout.')->toContain('.sites-index__status--active')->toContain('.sites-index__status--inactive')->toContain(':root[data-admin-theme="dark"]')->toContain(':root[data-admin-theme="ocean"]')->toContain('@media (prefers-contrast: more)');
});
