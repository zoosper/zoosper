<?php

declare(strict_types=1);

use Marko\Session\File\Handler\FileSessionHandler;

it('keeps the concrete third-party session driver inside the Zoosper Session adapter', function (): void {
    $root = dirname(__DIR__, 4);
    $moduleComposer = json_decode((string) file_get_contents($root . '/app/zoosper-session/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $services = (string) file_get_contents($root . '/app/zoosper-session/config/services.php');
    $handler = new ReflectionClass(FileSessionHandler::class);
    $parameters = $handler->getConstructor()?->getParameters() ?? [];

    expect($moduleComposer['type'])->toBe('zoosper-module')
        ->and($moduleComposer['require'])->toHaveKey('marko/session-file', '0.8.5')
        ->and($services)->toContain('SessionHandlerInterface::class')
        ->toContain('new FileSessionHandler(')
        ->toContain('new SessionConfig($services->get(ConfigRepositoryInterface::class))')
        ->and(is_a(FileSessionHandler::class, SessionHandlerInterface::class, true))->toBeTrue()
        ->and($parameters)->toHaveCount(1)
        ->and((string) $parameters[0]->getType())->toBe(Marko\Session\Config\SessionConfig::class);
});


it('resolves session storage independently of the PHP-FPM working directory', function (): void {
    $root = dirname(__DIR__, 4);
    $configurationSource = (string) file_get_contents($root . '/app/zoosper-session/config/settings/session.php');

    expect($configurationSource)
        ->toContain('$basePath = dirname(__DIR__, 4);')
        ->toContain("env('SESSION_STORAGE_PATH', 'var/sessions')")
        ->toContain('$basePath . \'/\' . ltrim($configuredPath, \'/\\')')
        ->toContain("'path' => \$storagePath")
        ->not->toContain("'path' => trim((string) env('SESSION_STORAGE_PATH', 'var/sessions'))");
});


it('is visible to the current ModuleRegistry discovery contract', function (): void {
    $root = dirname(__DIR__, 4);
    $registry = new Zoosper\Core\Module\ModuleRegistry($root);
    $servicesPath = realpath($root . '/app/zoosper-session/config/services.php');
    $found = false;

    foreach ($registry->enabledModules() as $module) {
        if (realpath($module->configPath('services.php')) === $servicesPath) {
            $found = true;
            break;
        }
    }

    expect($root . '/app/zoosper-session/module.php')->toBeFile()
        ->and($found)->toBeTrue();
});










