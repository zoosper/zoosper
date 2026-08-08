<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('marks pinned views in the selector and refreshes pin changes across tabs', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("option.textContent='★ '+option.textContent")
        ->toContain('if(event.key===pinnedSavedViewsKey)')
        ->toContain("copyStatus.textContent='Pinned saved views updated in another tab'");
});
