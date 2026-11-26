<?php

declare(strict_types=1);

use Zoosper\Audit\Console\PruneLogsCommand;
use Zoosper\Core\Container\ServiceContainer;

it('registers the audit pruning command only in the audit module', function (): void {
    $root = dirname(__DIR__, 5);
    $adminServices = require $root . '/app/zoosper-admin/config/services.php';
    $auditServices = require $root . '/packages/zoosper-audit/config/services.php';
    $commands = require $root . '/packages/zoosper-audit/config/console.php';
    $container = new ServiceContainer();
    $container->set(PDO::class, new PDO('sqlite::memory:'));
    foreach ($auditServices as $id => $factory) {
        $container->factory($id, $factory);
    }

    expect($adminServices)->not->toHaveKey('Zoosper\\Admin\\Console\\PruneLogsCommand')
        ->and($commands)->toContain(PruneLogsCommand::class)
        ->and($container->get(PruneLogsCommand::class))->toBeInstanceOf(PruneLogsCommand::class);
});