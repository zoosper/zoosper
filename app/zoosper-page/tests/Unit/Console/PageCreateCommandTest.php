<?php

declare(strict_types=1);

use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Page\Console\PageCreateCommand;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Console/kernel decoupling phase regression test.
 *
 * File placement: app/zoosper-page/tests/Unit/Console/PageCreateCommandTest.php
 * — same depth as other per-module tests (5 levels up to repo root).
 *
 * NOTE: helper function names are prefixed with "pageCommandTest" to avoid
 * collisions with sibling test files — see AdminCreateCommandTest.php for
 * the full explanation.
 */
function pageCommandTestDatabase(): PDO
{
    $basePath = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    return $pdo;
}

/** @return array{0: ConsoleOutput, 1: resource, 2: resource} */
function pageCommandTestOutput(): array
{
    $stdout = fopen('php://memory', 'w+');
    $stderr = fopen('php://memory', 'w+');

    return [new ConsoleOutput($stdout, $stderr), $stdout, $stderr];
}

function pageCommandTestReadStream($stream): string
{
    rewind($stream);

    return (string) stream_get_contents($stream);
}

it('creates a published page on an existing site and prints confirmation', function (): void {
    $pdo = pageCommandTestDatabase();
    $sites = new SiteRepository($pdo);
    $pages = new PageRepository($pdo);
    $sites->create('testsite', 'Test Site', 'testsite.example.test');

    $command = new PageCreateCommand($sites, $pages);

    [$output, $stdout] = pageCommandTestOutput();
    $exitCode = $command->run(['--site=testsite', '--title=About Us', '--content=Hello there.'], $output);

    expect($exitCode)->toBe(0);
    expect(pageCommandTestReadStream($stdout))->toContain('Created published page');

    $site = $sites->findByCode('testsite');
    $page = $pages->findPublishedBySlug($site->id, 'about-us');
    expect($page)->not->toBeNull();
    expect($page->status)->toBe('published');
});

it('rejects a non-existent site with a clear message and non-zero exit code', function (): void {
    $pdo = pageCommandTestDatabase();
    $sites = new SiteRepository($pdo);
    $pages = new PageRepository($pdo);
    $command = new PageCreateCommand($sites, $pages);

    [$output, , $stderr] = pageCommandTestOutput();
    $exitCode = $command->run(['--site=doesnotexist', '--title=Orphan Page'], $output);

    expect($exitCode)->toBe(1);
    expect(pageCommandTestReadStream($stderr))->toContain('Site does not exist');
});

it('fails with a clear message when required options are missing', function (): void {
    $pdo = pageCommandTestDatabase();
    $sites = new SiteRepository($pdo);
    $pages = new PageRepository($pdo);
    $command = new PageCreateCommand($sites, $pages);

    [$output, , $stderr] = pageCommandTestOutput();
    $exitCode = $command->run(['--site=main'], $output);

    expect($exitCode)->toBe(1);
    expect(pageCommandTestReadStream($stderr))->toContain('Missing required option');
});










