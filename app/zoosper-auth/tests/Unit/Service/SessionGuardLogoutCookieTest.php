<?php

declare(strict_types=1);

it('expires the browser session cookie with the active cookie policy before destroying the session', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-auth/src/Service/SessionGuard.php');
    $cookie = strpos($source, 'setcookie($sessionName');
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

it('clears authentication and handles session cookie configuration and name failures', function (string $cookies, string $name, bool $expectCookie): void {
    $fixture = dirname(__DIR__, 2) . '/Fixtures/session_guard_logout.php';
    $process = proc_open([PHP_BINARY, $fixture, $cookies, $name], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    expect($process)->not->toBeFalse();
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    expect(proc_close($process))->toBe(0, $error);
    $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    expect($result['session'])->toBe([])
        ->and($result['destroyed'])->toBeTrue()
        ->and($result['cookie'] !== null)->toBe($expectCookie);
    if ($expectCookie) {
        expect($result['cookie']['name'])->toBe('ADMINSESSID')
            ->and($result['cookie']['value'])->toBe('')
            ->and($result['cookie']['options']['path'])->toBe('/admin')
            ->and($result['cookie']['options']['domain'])->toBe('example.test')
            ->and($result['cookie']['options']['secure'])->toBeTrue()
            ->and($result['cookie']['options']['httponly'])->toBeTrue()
            ->and($result['cookie']['options']['samesite'])->toBe('Strict')
            ->and($result['cookie']['options']['expires'])->toBeLessThan(time());
    }
})->with([['1', 'valid', true], ['0', 'valid', false], ['', 'valid', false], ['false', 'valid', false], ['1', 'false', false], ['1', 'empty', false]]);










