<?php

declare(strict_types=1);

use Zoosper\Auth\Console\AdminCreateCommand;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Console/kernel decoupling phase regression test.
 *
 * File placement: app/zoosper-auth/tests/Unit/Console/AdminCreateCommandTest.php
 * — same depth as other per-module tests (5 levels up to repo root).
 *
 * Builds a real, fresh in-memory SQLite database via the actual Migrator +
 * ModuleRegistry (same approach as the Phase 1.40c migration test) so the
 * command runs against real schema, not a hand-rolled fixture. Never touches
 * your real MySQL database.
 *
 * NOTE: helper function names below are prefixed with "authCommandTest" to
 * avoid collisions with identically-purposed helpers in the sibling
 * SiteCreateCommandTest.php / PageCreateCommandTest.php files — Pest runs
 * all test files in a single PHP process, so global function names must be
 * unique across the whole suite.
 */
function authCommandTestDatabase(): PDO
{
    $basePath = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    return $pdo;
}

/** @return array{0: ConsoleOutput, 1: resource, 2: resource} */
function authCommandTestOutput(): array
{
    $stdout = fopen('php://memory', 'w+');
    $stderr = fopen('php://memory', 'w+');

    return [new ConsoleOutput($stdout, $stderr), $stdout, $stderr];
}

function authCommandTestReadStream($stream): string
{
    rewind($stream);

    return (string) stream_get_contents($stream);
}

it('creates a super admin user and prints confirmation', function (): void {
    $pdo = authCommandTestDatabase();
    $users = new AdminUserRepository($pdo);
    $command = new AdminCreateCommand($users, new PasswordHasher());

    [$output, $stdout] = authCommandTestOutput();
    $exitCode = $command->run(['--email=newadmin@example.test', '--password=ChangeMe123!', '--name=New Admin'], $output);

    expect($exitCode)->toBe(0);
    expect(authCommandTestReadStream($stdout))->toContain('Created super admin user');
    expect($users->findByEmail('newadmin@example.test'))->not->toBeNull();
});

it('rejects a duplicate email with a clear message and non-zero exit code', function (): void {
    $pdo = authCommandTestDatabase();
    $users = new AdminUserRepository($pdo);
    $command = new AdminCreateCommand($users, new PasswordHasher());

    [$output1] = authCommandTestOutput();
    $command->run(['--email=dupe@example.test', '--password=ChangeMe123!'], $output1);

    [$output2, , $stderr2] = authCommandTestOutput();
    $exitCode = $command->run(['--email=dupe@example.test', '--password=ChangeMe123!'], $output2);

    expect($exitCode)->toBe(1);
    expect(authCommandTestReadStream($stderr2))->toContain('already exists');
});

it('fails with a clear message when required options are missing', function (): void {
    $pdo = authCommandTestDatabase();
    $users = new AdminUserRepository($pdo);
    $command = new AdminCreateCommand($users, new PasswordHasher());

    [$output, , $stderr] = authCommandTestOutput();
    $exitCode = $command->run(['--email=onlyemail@example.test'], $output);

    expect($exitCode)->toBe(1);
    expect(authCommandTestReadStream($stderr))->toContain('Missing required option');
});
