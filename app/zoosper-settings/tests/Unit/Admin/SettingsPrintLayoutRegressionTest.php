<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('normalises print width across common admin-shell containers', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('body,html,.admin-layout,.admin-main,.admin-content,.content-wrapper,main{width:100%!important;max-width:none!important')
        ->toContain('.settings-workspace{display:block!important;width:100%!important;max-width:none!important')
        ->toContain('.settings-content{width:100%!important;max-width:none!important');
});

it('uses deterministic category page breaks without forcing a trailing page', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('.settings-category+.settings-category{break-before:page!important;page-break-before:always!important}')
        ->toContain('.settings-category:last-child,.settings-section:last-child,.settings-group:last-child,.settings-field:last-child{break-after:auto!important;page-break-after:auto!important;margin-bottom:0!important}')
        ->not->toContain('.settings-category{break-before:page}');
});

it('keeps fields intact and removes target outlines during print', function (): void {
    $root=dirname(__DIR__,5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('break-inside:avoid-page;page-break-inside:avoid')
        ->toContain('.settings-field:target,.settings-field.settings-match,.settings-field.settings-current-match{outline:none!important;background:transparent!important}')
        ->toContain('scroll-margin-top:8rem');
});
