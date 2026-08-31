<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('provides previous and next navigation for filtered search matches', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('id="settings-previous-match" disabled')
        ->toContain('id="settings-next-match" disabled')
        ->toContain('id="settings-match-position"')
        ->toContain("previousMatch.addEventListener('click'")
        ->toContain("nextMatch.addEventListener('click'");
});

it('supports Enter and Shift Enter while search owns focus', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("input.addEventListener('keydown'")
        ->toContain("event.key==='Enter'")
        ->toContain("event.shiftKey?-1:1")
        ->toContain("matchPosition.textContent=(currentMatchIndex+1)+' of '+matchingFields.length");
});

it('uses one strong current match instead of outlining every result', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('settings-current-match')
        ->toContain("field.classList.toggle('settings-current-match'")
        ->toContain('.settings-field.settings-match{background:#f8faff')
        ->toContain('.settings-field.settings-current-match{outline:3px solid #6366f1');
});

it('removes match decoration from print output', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain('.settings-field:target,.settings-field.settings-match,.settings-field.settings-current-match{outline:none!important;background:transparent!important}');
});










