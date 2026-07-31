<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\DuplicateModuleException;
use Zoosper\Core\Module\ModuleRegistry;

function moduleConflictFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-module-conflict-' . bin2hex(random_bytes(6));
    mkdir($root . '/app/first', 0775, true);
    mkdir($root . '/app/second', 0775, true);

    foreach (['first', 'second'] as $directory) {
        file_put_contents(
            $root . '/app/' . $directory . '/module.php',
            "<?php return ['name' => 'shared-module', 'enabled' => true];",
        );
    }

    return $root;
}

test('same-layer duplicate module identities fail loudly', function (): void {
    expect(fn (): array => (new ModuleRegistry(moduleConflictFixture()))->discoverModulesLive())
        ->toThrow(DuplicateModuleException::class, 'Duplicate module identity "shared-module"');
});

function moduleOverrideFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-module-override-' . bin2hex(random_bytes(6));
    mkdir($root . '/app/project-module', 0775, true);
    mkdir($root . '/vendor/example/project-module', 0775, true);

    file_put_contents(
        $root . '/app/project-module/module.php',
        "<?php return ['name' => 'project-module', 'enabled' => true];",
    );
    file_put_contents(
        $root . '/vendor/example/project-module/module.php',
        "<?php return ['name' => 'project-module', 'enabled' => true];",
    );
    file_put_contents(
        $root . '/vendor/example/project-module/composer.json',
        json_encode(['extra' => ['zoosper' => ['module' => 'module.php']]], JSON_THROW_ON_ERROR),
    );

    return $root;
}

test('higher-priority app module overrides the same vendor identity', function (): void {
    $modules = (new ModuleRegistry(moduleOverrideFixture()))->discoverModulesLive();

    expect($modules)->toHaveCount(1);
    expect($modules[0]->name)->toBe('project-module');
    expect($modules[0]->source)->toBe('app');
});
