<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('supports apply delete and set-default keyboard commands from the saved-view selector', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("savedView.addEventListener('keydown'")
        ->toContain("event.key==='Enter'")
        ->toContain("event.key==='Delete'")
        ->toContain("event.key==='d'&&event.altKey")
        ->toContain("savedView.dispatchEvent(new Event('change'))")
        ->toContain('deleteView.click()')
        ->toContain('defaultView.click()');
});










