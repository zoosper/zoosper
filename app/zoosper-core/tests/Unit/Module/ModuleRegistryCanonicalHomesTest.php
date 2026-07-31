<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function canonicalHomesFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-canonical-homes-' . bin2hex(random_bytes(6));

    mkdir($root . '/packages/source-only', 0775, true);
    file_put_contents(
        $root . '/packages/source-only/module.php',
        "<?php return ['name' => 'source-only', 'enabled' => true];",
    );

    mkdir($root . '/vendor/acme/installed', 0775, true);
    file_put_contents(
        $root . '/vendor/acme/installed/composer.json',
        json_encode([
            'name' => 'acme/installed',
            'type' => 'zoosper-module',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents(
        $root . '/vendor/acme/installed/module.php',
        "<?php return ['name' => 'installed', 'enabled' => true];",
    );

    return $root;
}

test('packages is source layout only and vendor is the runtime package home', function (): void {
    $modules = (new ModuleRegistry(canonicalHomesFixture()))->discoverModulesLive();
    $names = array_map(static fn ($module): string => $module->name, $modules);

    expect($names)->toContain('installed');
    expect($names)->not->toContain('source-only');
    expect($modules[0]->source)->toBe('vendor');
});
