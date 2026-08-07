<?php

declare(strict_types=1);

it('keeps production web session policy centralised in Core Application', function (): void {
    $root = dirname(__DIR__, 5);
    $application = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Application.php');

    expect($application)->toContain('session_set_cookie_params([')
        ->toContain('session_start();')
        ->toContain("ini_set('session.use_strict_mode', '1')")
        ->toContain("ini_set('session.use_only_cookies', '1')")
        ->toContain("ini_set('session.use_trans_sid', '0')");

    foreach (glob($root . '/app/*/src/**/*.php') ?: [] as $file) {
        if ($file === $root . '/app/zoosper-core/src/Http/Application.php') {
            continue;
        }
        expect((string) file_get_contents($file))->not->toContain('session_set_cookie_params(');
    }
});
