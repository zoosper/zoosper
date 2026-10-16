<?php

declare(strict_types=1);

it('locks the third bounded Psalm public-boundary cohort', function (): void {
    $root = dirname(__DIR__, 5);
    $request = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Request.php');
    $sites = (string) file_get_contents($root . '/app/zoosper-core/src/Site/SiteContextResolver.php');
    $forms = (string) file_get_contents($root . '/packages/zoosper-admin-form/src/AdminFormUiConfigLoader.php');

    expect($request)
        ->toContain('* @psalm-api')
        ->not->toContain('if (is_string($key) && $key !== \'\')')
        ->and($sites)
        ->toContain('if ($host !== \'\')')
        ->not->toContain('$this->sites !== null')
        ->not->toContain('trim((string) ($prefix ?? \'\'))')
        ->and($forms)
        ->toContain('is_bool($discovered) ? $discovered')
        ->toContain('if ($hasUi !== true)')
        ->toContain('if ($hasUi === true)');
});
