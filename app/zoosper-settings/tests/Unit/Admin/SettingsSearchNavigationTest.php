<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('uses one strong current match and removes search decoration from print', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('.settings-field.settings-match{background:#f8faff')
        ->toContain('.settings-field.settings-current-match{outline:3px solid #6366f1')
        ->toContain('.settings-field:target,.settings-field.settings-match,.settings-field.settings-current-match{outline:none!important;background:transparent!important}');
});
