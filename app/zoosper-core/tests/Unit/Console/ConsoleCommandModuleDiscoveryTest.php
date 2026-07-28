<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Console\ModuleConsoleCommandLoader;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Core\Log\LogManager;
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
 * Rather than requiring bootstrap/autoload.php directly (risky — if it
 * re-requires vendor/autoload.php, which Pest has already loaded, PHP would
 * fatal with "cannot redeclare class" across the WHOLE test suite, not just
 * this test), define a minimal, guarded fallback here instead. This test
 * only needs config loading to succeed without crashing — it never actually
 * connects using config/database.php's values, since it builds its own
 * in-memory SQLite PDO directly. A getenv()-based shim is sufficient and
 * completely safe.
 *
 * File placement: app/zoosper-core/tests/Unit/Console/ConsoleCommandModuleDiscoveryTest.php
 * — same depth (5 levels up) as ModuleOwnedMigrationDiscoveryTest.php.
 */
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        return $value !== false ? $value : $default;
    }
}

it('discovers admin:create, site:create and page:create as module-owned console commands', function (): void {
    $basePath = dirname(__DIR__, 5);

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $modules = new ModuleRegistry($basePath);
    (new Migrator($pdo, $basePath, $modules))->migrate();

    $config = ConfigRepository::fromPath($basePath . '/config');

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
