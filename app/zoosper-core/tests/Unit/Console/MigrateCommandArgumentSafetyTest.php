<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Console;

use PDO;
use Zoosper\Core\Console\BuiltIn\MigrateCommand;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Database\Migrator;

it('fails closed instead of treating dry-run as a real migrate option', function (): void {
    $root = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $stdout = fopen('php://memory', 'w+');
    $stderr = fopen('php://memory', 'w+');
    $command = new MigrateCommand(new Migrator($pdo, $root, new ModuleRegistry($root)));

    expect($command->run(['--dry-run'], new ConsoleOutput($stdout, $stderr)))->toBe(2);
    rewind($stderr);
    expect((string) stream_get_contents($stderr))->toContain('has no dry-run mode');
    expect($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'")->fetchColumn())->toBeFalse();
});
