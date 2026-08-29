<?php

declare(strict_types=1);

/** @return array<string, string> */
function quickStartEnvironmentValues(string $path): array
{
    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $values[trim($name)] = trim($value);
    }

    return $values;
}

it('keeps the documented local HTTP environment session-compatible and non-throwing', function (): void {
    $root = dirname(__DIR__, 5);
    $env = quickStartEnvironmentValues($root . '/.env.example');

    expect($env['APP_ENV'] ?? null)->toBe('local')
        ->and($env['APP_URL'] ?? null)->toBe('http://127.0.0.1:8080')
        ->and($env['SESSION_SECURE'] ?? null)->toBe('false')
        ->and($env['RATE_LIMIT_ENABLED'] ?? null)->toBe('false')
        ->and($env['RATE_LIMIT_MODE'] ?? null)->toBe('report_only')
        ->and($env['RATE_LIMIT_IDENTITY_SALT'] ?? null)->toBe('');
});

it('routes local development requests through the public front controller', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $router = (string) file_get_contents($root . '/public/router.php');

    expect($composer['scripts']['serve'] ?? null)->toBe('@php -S 127.0.0.1:8080 -t public public/router.php')
        ->and($router)->toContain("require __DIR__ . '/index.php';")
        ->toContain('str_starts_with($candidate, $publicRoot . DIRECTORY_SEPARATOR)')
        ->toContain('return false;');
});
