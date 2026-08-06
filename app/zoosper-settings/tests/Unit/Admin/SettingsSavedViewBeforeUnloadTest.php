<?php

declare(strict_types=1);

it('warns before leaving the page with dirty settings forms', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain("window.addEventListener('beforeunload'")
        ->toContain('if(!hasDirtySettingsForms())return')
        ->toContain("event.returnValue=''" )
        ->toContain("form.dataset.dirty='false'");
});
