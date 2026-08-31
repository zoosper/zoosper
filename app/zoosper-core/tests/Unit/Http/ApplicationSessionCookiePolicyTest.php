<?php

declare(strict_types=1);

use Zoosper\Core\Http\Application;

it('normalises SameSite values and rejects unsafe unknown values', function (): void {
    expect(Application::normaliseSameSite('Lax'))->toBe('Lax')
        ->and(Application::normaliseSameSite(' strict '))->toBe('Strict')
        ->and(Application::normaliseSameSite('NONE'))->toBe('None')
        ->and(Application::normaliseSameSite('invalid'))->toBe('Lax')
        ->and(Application::normaliseSameSite(''))->toBe('Lax');
});

it('keeps SameSite None conditional on a secure cookie in the bootstrap source', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Application.php');

    expect($source)->toContain("if (\$sameSite === 'None' && !\$secure)")
        ->toContain("\$sameSite = 'Lax'")
        ->toContain("'secure' => \$secure")
        ->toContain("'httponly' => true");
});










