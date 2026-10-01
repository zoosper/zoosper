<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('keeps reset deep-link and filtered-count presentation contracts', function (): void {
    $root = dirname(__DIR__, 5);
    $view = settingsPresentationBundle($root);

    expect($view)->toContain('id="settings-reset-view"')
        ->toContain('$scopeType === \'default\'')
        ->toContain('class="settings-hidden"')
        ->toContain('data-category-count')
        ->toContain('data-total=');
});
