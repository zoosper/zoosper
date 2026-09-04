<?php

declare(strict_types=1);

it('composes existing Login History result nodes into one responsive surface', function (): void {
    $root = dirname(__DIR__, 3);
    $script = (string) file_get_contents($root . '/resources/admin/js/login-history-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/login-history-workspace.css');

    expect($script)
        ->toContain("footer.dataset.loginHistoryPagination = ''")
        ->toContain("table.insertAdjacentElement('afterend', footer)")
        ->toContain('legacyNavigation?.remove()')
        ->toContain('Node.DOCUMENT_POSITION_FOLLOWING')
        ->not->toContain('.admin-topbar__title')
        ->not->toContain('innerHTML')
        ->not->toContain('cloneNode')
        ->and($css)
        ->toContain('.login-history-index__summary')
        ->toContain('.login-history-index__table')
        ->toContain('.login-history-index__pagination')
        ->toContain('grid-template-columns: minmax(7rem, 1fr) auto minmax(7rem, 1fr);');
});
