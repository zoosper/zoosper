<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('keeps connected tab panel group and responsive navigation semantics', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('aria-controls="category-panel-')
        ->toContain('role="tabpanel"')
        ->toContain('aria-labelledby="category-tab-')
        ->toContain('data-group-key')
        ->toContain('.settings-scope{position:sticky')
        ->toContain('.settings-nav{position:sticky;top:4.6rem')
        ->toContain('@media(max-width:850px)');
});
