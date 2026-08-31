<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('keeps help and More actions mutually exclusive', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain("helpDisclosure=document.querySelector('.settings-help')")
        ->toContain("actionsDisclosure=document.querySelector('.settings-more-actions')")
        ->toContain('const closeFloatingPanels=(except,restoreFocus=false)=>')
        ->toContain("if(panel.open){lastOpenedDisclosure=panel;closeFloatingPanels(panel)");
});

it('closes floating panels on outside pointer, Escape, category activation and print', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain("document.addEventListener('pointerdown'")
        ->toContain("event.key==='Escape'")
        ->toContain('function activate(category,focus=false,updateHash=true){closeFloatingPanels(null);')
        ->toContain("window.addEventListener('beforeprint',()=>{closeFloatingPanels(null);");
});










