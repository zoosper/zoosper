<?php

declare(strict_types=1);

it('expires the browser session cookie with the active cookie policy before destroying the session', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-auth/src/Service/SessionGuard.php');
    $cookie = strpos($source, 'setcookie(session_name()');
    $destroy = strpos($source, 'session_destroy();');

    expect($cookie)->not->toBeFalse()
        ->and($destroy)->not->toBeFalse()
        ->and($cookie)->toBeLessThan($destroy)
        ->and($source)->toContain("'path' => \$params['path']")
        ->toContain("'domain' => \$params['domain']")
        ->toContain("'secure' => \$params['secure']")
        ->toContain("'httponly' => \$params['httponly']")
        ->toContain("'samesite' => \$params['samesite'] ?? 'Lax'");
});










