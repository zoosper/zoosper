<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('keeps value-free field fragments and strong disclosure focus treatment', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->not->toContain('data-setting-value')
        ->toContain('.settings-help summary:focus-visible')
        ->toContain('.settings-more-actions summary:focus-visible');
});
