<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use ErrorException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Console\ModuleConsoleCommandLoader;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Event\EventDispatcher;
use Zoosper\Core\Event\ModuleEventListenerLoader;
use Zoosper\Core\Http\Middleware\ModuleAdminMiddlewareLoader;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\Module;
use Zoosper\Core\Module\ModuleRegistry;

final class DiscoveryFlagCommand implements ConsoleCommandInterface
{
    public function name(): string
    {
        return 'test:discovery';
    }

    public function description(): string
    {
        return 'Discovery test command';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        return 0;
    }
}

final class DiscoveryFlagMiddleware implements RouteMiddleware
{
    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        return $next($request);
    }
}

function discoveryFlagRegistry(string $root, array $discovery): ModuleRegistry
{
    $registry = new ModuleRegistry($root);
    $property = new \ReflectionProperty(ModuleRegistry::class, 'cachedModules');
    $property->setValue($registry, [new Module('DiscoveryTest', $root, discovery: $discovery)]);

    return $registry;
}

function loadDiscoveryFlagFixture(string $kind, ModuleRegistry $registry): int
{
    $services = new ServiceContainer();

    if ($kind === 'console') {
        return count((new ModuleConsoleCommandLoader($registry, $services))->load());
    }

    if ($kind === 'events') {
        $dispatcher = new EventDispatcher();
        (new ModuleEventListenerLoader($registry, $services))->attach($dispatcher);

        return count($dispatcher->listeners('test.event'));
    }

    return count((new ModuleAdminMiddlewareLoader($registry, $services))->load());
}

test('discovery flags control loading and fall back to the filesystem', function (string $kind, bool $hasFlag, mixed $flag, bool $fileExists, int $expected) {
    $root = sys_get_temp_dir() . '/zoosper-discovery-flags-' . bin2hex(random_bytes(8));
    mkdir($root . '/config', 0775, true);
    $file = $root . '/config/' . $kind . '.php';
    $contents = match ($kind) {
        'console' => '<?php return [\\' . DiscoveryFlagCommand::class . '::class];',
        'events' => "<?php return ['test.event' => [static function (): void {}]];",
        'admin_middleware' => '<?php return [\\' . DiscoveryFlagMiddleware::class . '::class];',
    };

    try {
        if ($fileExists) {
            file_put_contents($file, $contents);
        }

        $discovery = $hasFlag ? [$kind => $flag] : [];
        expect(loadDiscoveryFlagFixture($kind, discoveryFlagRegistry($root, $discovery)))->toBe($expected);
    } finally {
        if (is_file($file)) {
            unlink($file);
        }
        rmdir($root . '/config');
        rmdir($root);
    }
})->with(['console', 'events', 'admin_middleware'])->with([
    'missing with file' => [false, null, true, 1],
    'missing without file' => [false, null, false, 0],
    'null with file' => [true, null, true, 1],
    'null without file' => [true, null, false, 0],
    'false' => [true, false, true, 0],
    'true' => [true, true, true, 1],
    'integer zero' => [true, 0, true, 0],
    'integer one' => [true, 1, true, 1],
    'string zero' => [true, '0', true, 0],
    'string one' => [true, '1', true, 1],
]);

test('truthy discovery flags attempt to require a missing config file', function (string $kind, mixed $flag) {
    $root = sys_get_temp_dir() . '/zoosper-discovery-flags-' . bin2hex(random_bytes(8));
    $registry = discoveryFlagRegistry($root, [$kind => $flag]);

    set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    try {
        expect(fn () => loadDiscoveryFlagFixture($kind, $registry))->toThrow(ErrorException::class, 'Failed to open stream');
    } finally {
        restore_error_handler();
    }
})->with(['console', 'events', 'admin_middleware'])->with([true, 1, '1']);