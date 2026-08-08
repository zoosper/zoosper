<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('warns before leaving the page with dirty settings forms', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("window.addEventListener('beforeunload'")
        ->toContain('if(!hasDirtySettingsForms())return')
        ->toContain("event.returnValue=''" )
        ->toContain("form.dataset.dirty='false'");
});
