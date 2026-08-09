<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function legacyOnlyVendorFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-legacy-only-' . bin2hex(random_bytes(6));
    $package = $root . '/vendor/acme/legacy';
    mkdir($package, 0775, true);

    file_put_contents(
        $package . '/composer.json',
        json_encode([
            'name' => 'acme/legacy',
            'type' => 'zoosper-module',
            'extra' => ['zoosper' => ['module' => 'module.php']],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents(
        $package . '/module.php',
        "<?php return ['name' => 'legacy-only', 'enabled' => true];",
    );

    return $root;
}

test('Zoosper package type remains authoritative when legacy metadata is present', function (): void {
    $modules = (new ModuleRegistry(legacyOnlyVendorFixture()))->discoverModulesLive();
    expect($modules)->toHaveCount(1)
        ->and($modules[0]->name)->toBe('acme-legacy')
        ->and($modules[0]->source)->toBe('vendor');
});
