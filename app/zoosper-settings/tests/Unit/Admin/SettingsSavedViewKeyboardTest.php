<?php

declare(strict_types=1);

it('supports apply delete and set-default keyboard commands from the saved-view selector', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain("savedView.addEventListener('keydown'")
        ->toContain("event.key==='Enter'")
        ->toContain("event.key==='Delete'")
        ->toContain("event.key==='d'&&event.altKey")
        ->toContain("savedView.dispatchEvent(new Event('change'))")
        ->toContain('deleteView.click()')
        ->toContain('defaultView.click()');
});
