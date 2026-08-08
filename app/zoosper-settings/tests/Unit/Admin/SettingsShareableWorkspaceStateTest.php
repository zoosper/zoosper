<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('encodes only value-free workspace state in shareable links', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("const workspaceState=()=>({q:input.value.trim(),view:sourceFilter.value,module:moduleFilter.value,density:density.value})")
        ->toContain("['q','view','module','density'].forEach")
        ->toContain('url.searchParams.set(key,value)')
        ->toContain("const workspaceState=()=>({q:input.value.trim(),view:sourceFilter.value,module:moduleFilter.value,density:density.value})")
        ->not->toContain('url.searchParams.set(\'settings\'')
        ->not->toContain('data-copy-setting-value');
});

it('validates link state against available control options before applying', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->not->toContain('id="settings-apply-url-state"')
        ->toContain('[...sourceFilter.options].some(option=>option.value===view)')
        ->toContain('[...moduleFilter.options].some(option=>option.value===module)')
        ->toContain('[...density.options].some(option=>option.value===densityValue)')
        ->toContain("linkState.textContent='Applied workspace state from link'");
});

it('clears workspace query state while preserving scope and fragment', function (): void {
    $root=dirname(__DIR__,5);$view = settingsPresentationBundle($root);
    expect($view)->toContain("['q','view','module','density'].forEach(key=>resetUrl.searchParams.delete(key))")
        ->toContain('resetUrl.pathname+resetUrl.search+resetUrl.hash')
        ->toContain("linkState.textContent='Workspace link state cleared'");
});
