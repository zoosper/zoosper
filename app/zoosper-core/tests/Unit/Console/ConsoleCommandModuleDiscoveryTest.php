<?php

declare(strict_types=1);

use Zoosper\Core\Config\ApplicationConfigLoader;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Console\ModuleConsoleCommandLoader;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Database\Migrator;
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Logger\Manager\LogManager;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Console/kernel decoupling phase — proves the whole wiring bin/zoosper
 * relies on at runtime (ConfigRepository, ServiceContainer,
 * ServiceProviderLoader, ModuleConsoleCommandLoader) actually surfaces
 * admin:create, site:create, and page:create as module-owned commands,
 * without bin/zoosper hardcoding any of them.
 *
 * This mirrors bin/zoosper's own loadModuleCommands() function exactly, so a
 * passing test here is strong evidence the real CLI will behave the same
 * way. Uses a fresh in-memory SQLite database — never touches your real
 * MySQL database.
 *
 * FIX: ConfigRepository::fromPath() loads config/database.php, which calls
 * the global env() helper. That helper is defined somewhere inside
 * bootstrap/autoload.php, which bin/zoosper requires before ever touching
 * ConfigRepository — but Pest's own bootstrap does not load that file, so
 * env() is undefined when this test runs in isolation.
 *
 * Load the canonical application bootstrap through require_once. A test-local
 * global env() declaration cannot be removed after the test and changes how
 * later application boot resolves environment-backed configuration.
 *
 * File placement: app/zoosper-core/tests/Unit/Console/ConsoleCommandModuleDiscoveryTest.php
 * — same depth (5 levels up) as ModuleOwnedMigrationDiscoveryTest.php.
 */
require_once dirname(__DIR__, 5) . '/bootstrap/autoload.php';

it('discovers admin:create, site:create and page:create as module-owned console commands', function (): void {
    $basePath = dirname(__DIR__, 5);

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $modules = new ModuleRegistry($basePath);
    (new Migrator($pdo, $basePath, $modules))->migrate();

    $config = (new ApplicationConfigLoader($basePath, $modules))->load();

    $services = new ServiceContainer();
    $services->set(ConfigRepository::class, $config);
    $services->set(ModuleRegistry::class, $modules);
    $services->set(PDO::class, $pdo);

    $logManager = new LogManager($config, $basePath);
    $errorHandler = new ErrorHandler($logManager->exceptions());
    $services->set(LogManager::class, $logManager);
    $services->set(ErrorHandler::class, $errorHandler);
    $services->set('logger.default', $logManager->default());
    $services->set('logger.exception', $logManager->exceptions());

    (new ServiceProviderLoader($modules, $services))->register();

    $commands = (new ModuleConsoleCommandLoader($modules, $services))->load();

    expect($commands)->toHaveKey('admin:create');
    expect($commands)->toHaveKey('site:create');
    expect($commands)->toHaveKey('page:create');
});










