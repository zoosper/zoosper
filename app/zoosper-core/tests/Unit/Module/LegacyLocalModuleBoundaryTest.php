<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function legacyLocalModuleBoundaryFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-local-module-boundary-' . bin2hex(random_bytes(6));
    $localPath = $root . '/app/code/Lowes/Auspost';
    $firstPartyPath = $root . '/app/acme-blog';

    mkdir($localPath, 0775, true);
    mkdir($firstPartyPath, 0775, true);

    file_put_contents(
        $localPath . '/composer.json',
        json_encode([
            'name' => 'lowes/auspost',
            'type' => 'library',
            'autoload' => ['psr-4' => ['Lowes\Auspost\' => 'src/']],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents(
        $localPath . '/module.php',
        "<?php return ['name' => 'Lowes_Auspost', 'enabled' => true];\n",
    );

    file_put_contents(
        $firstPartyPath . '/composer.json',
        json_encode([
            'name' => 'acme/blog',
            'type' => 'zoosper-module',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents($firstPartyPath . '/module.php', "<?php return [];\n");

    return $root;
}

test('nested app code is outside the canonical first-party runtime layer', function (): void {
    $modules = (new ModuleRegistry(legacyLocalModuleBoundaryFixture()))->discoverModulesLive();
    $names = array_map(static fn ($module): string => $module->name, $modules);

    expect($names)->toContain('acme-blog');
    expect($names)->not->toContain('Lowes_Auspost');
    expect($names)->not->toContain('lowes-auspost');
});

test('first-party architecture scope uses direct package homes only', function (): void {
    $root = legacyLocalModuleBoundaryFixture();
    $manifests = array_merge(
        glob($root . '/app/*/composer.json') ?: [],
        glob($root . '/packages/*/composer.json') ?: [],
    );

    expect($manifests)->toBe([$root . '/app/acme-blog/composer.json']);
    expect($manifests)->not->toContain($root . '/app/code/Lowes/Auspost/composer.json');
});










