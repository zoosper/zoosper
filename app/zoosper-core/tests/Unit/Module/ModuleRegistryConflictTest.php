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










