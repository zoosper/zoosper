<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Console\ConsoleApplication;
use Zoosper\Core\Console\ConsoleKernel;
use Zoosper\Core\Console\ConsoleServiceFactory;
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Database\PdoConnectionProvider;
use Zoosper\Logger\Manager\LogManager;

test('console kernel validates the module registry supplied by service providers', function (bool $invalid): void {
    $root = dirname(__DIR__, 5) . '/var/console-registry-test-' . bin2hex(random_bytes(6));
    mkdir($root . '/app/example/config', 0775, true);
    file_put_contents($root . '/app/example/module.php', "<?php return ['name' => 'Example', 'enabled' => true];");
    if ($invalid) {
        file_put_contents($root . '/app/example/config/services.php', '<?php return [\\Zoosper\\Core\\Module\\ModuleRegistry::class => new \\stdClass()];');
    }

    try {
        $config = ConfigRepository::fromArray([]);
        $logs = new LogManager($config, $root);
        $factory = new ConsoleServiceFactory(
            $root,
            $config,
            new ModuleRegistry($root),
            new PdoConnectionProvider(static fn (): PDO => new PDO('sqlite::memory:')),
            $logs,
            new ErrorHandler($logs->exceptions()),
        );
        if ($invalid) {
            expect(fn () => (new ConsoleKernel($factory, []))->application())
                ->toThrow(TypeError::class, 'Service must be a ModuleRegistry');
        } else {
            expect((new ConsoleKernel($factory, []))->application())->toBeInstanceOf(ConsoleApplication::class);
        }
    } finally {
        if ($invalid) {
            unlink($root . '/app/example/config/services.php');
        }
        unlink($root . '/app/example/module.php');
        rmdir($root . '/app/example/config');
        rmdir($root . '/app/example');
        rmdir($root . '/app');
        rmdir($root);
    }
})->with([false, true]);