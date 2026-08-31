<?php

declare(strict_types=1);

use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Site\Console\SiteCreateCommand;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Console/kernel decoupling phase regression test.
 *
 * File placement: app/zoosper-site/tests/Unit/Console/SiteCreateCommandTest.php
 * — same depth as other per-module tests (5 levels up to repo root).
 *
 * NOTE: helper function names are prefixed with "siteCommandTest" to avoid
 * collisions with sibling test files — see AdminCreateCommandTest.php for
 * the full explanation.
 */
function siteCommandTestDatabase(): PDO
{
    $basePath = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    return $pdo;
}

/** @return array{0: ConsoleOutput, 1: resource, 2: resource} */
function siteCommandTestOutput(): array
{
    $stdout = fopen('php://memory', 'w+');
    $stderr = fopen('php://memory', 'w+');

    return [new ConsoleOutput($stdout, $stderr), $stdout, $stderr];
}

function siteCommandTestReadStream($stream): string
{
    rewind($stream);

    return (string) stream_get_contents($stream);
}

it('creates a site with its primary domain and prints confirmation', function (): void {
    $pdo = siteCommandTestDatabase();
    $sites = new SiteRepository($pdo);
    $command = new SiteCreateCommand($sites);

    [$output, $stdout] = siteCommandTestOutput();
    $exitCode = $command->run(['--code=secondsite', '--name=Second Site', '--host=second.example.test'], $output);

    expect($exitCode)->toBe(0);
    expect(siteCommandTestReadStream($stdout))->toContain('Created site');
    expect($sites->findByCode('secondsite'))->not->toBeNull();
});

it('rejects a duplicate site code with a clear message and non-zero exit code', function (): void {
    // NOTE: migrations create the `sites` table structure only — no default
    // site is seeded by any migration (the 'main' site in a real Zoosper
    // install is created by the app/installer, not a migration). So this
    // test creates its own first site, then attempts a duplicate.
    $pdo = siteCommandTestDatabase();
    $sites = new SiteRepository($pdo);
    $command = new SiteCreateCommand($sites);

    [$firstOutput] = siteCommandTestOutput();
    $command->run(['--code=dupecode', '--name=First', '--host=first.example.test'], $firstOutput);

    [$output, , $stderr] = siteCommandTestOutput();
    $exitCode = $command->run(['--code=dupecode', '--name=Duplicate', '--host=dupe.example.test'], $output);

    expect($exitCode)->toBe(1);
    expect(siteCommandTestReadStream($stderr))->toContain('already exists');
});










