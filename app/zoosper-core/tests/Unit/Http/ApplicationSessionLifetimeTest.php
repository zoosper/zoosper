<?php

declare(strict_types=1);

it('applies one bounded server and cookie session lifetime before session start', function (): void {
    $root = dirname(__DIR__, 5);
    $applicationPath = $root . '/app/zoosper-core/src/Http/Application.php';

    expect($applicationPath)->toBeFile();
    $source = file_get_contents($applicationPath);
    expect($source)->not->toBeFalse();

    expect($source)->toContain(<<<'PHP'
env('SESSION_LIFETIME_SECONDS', 28800)
PHP);
    expect($source)->toContain(<<<'PHP'
ini_set('session.gc_maxlifetime', (string) $sessionLifetime)
PHP);
    expect($source)->toContain(<<<'PHP'
'lifetime' => $sessionLifetime
PHP)
        ->toContain("ini_set('session.use_strict_mode', '1')")
        ->toContain('session_set_save_handler($this->sessionHandler, true)')
        ->toContain("ini_set('session.use_only_cookies', '1')")
        ->toContain("ini_set('session.use_trans_sid', '0')")
        ->toContain("ini_set('session.cookie_httponly', '1')");

    $configurationPosition = strpos(
        $source,
        <<<'PHP'
ini_set('session.gc_maxlifetime'
PHP,
    );
    $sessionStartPosition = strpos($source, 'session_start();');

    expect($configurationPosition)->not->toBeFalse();
    expect($sessionStartPosition)->not->toBeFalse();
    expect($configurationPosition)->toBeLessThan($sessionStartPosition);
});
