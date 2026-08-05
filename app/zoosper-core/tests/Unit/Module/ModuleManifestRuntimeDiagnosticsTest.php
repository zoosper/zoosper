<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleRegistry;

function runtimeDiagnosticsFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-manifest-diagnostics-' . bin2hex(random_bytes(6));
    $module = $root . '/app/acme-example';
    mkdir($module, 0775, true);
    file_put_contents(
        $module . '/module.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'acme-example', 'enabled' => true];\n",
    );

    return $root;
}

it('reports no rejection when a fresh compiled manifest is used', function (): void {
    $root = runtimeDiagnosticsFixture();
    $cachePath = $root . '/var/cache/modules.php';
    (new ModuleManifestCompiler($root, $cachePath))->compile();

    $registry = new ModuleRegistry($root, $cachePath);
    expect($registry->enabledModules())->toHaveCount(1)
        ->and($registry->compiledManifestRejectionReason())->toBeNull();

    exec('rm -rf ' . escapeshellarg($root));
});

it('reports a first-party module change and falls back to live discovery', function (): void {
    $root = runtimeDiagnosticsFixture();
    $cachePath = $root . '/var/cache/modules.php';
    (new ModuleManifestCompiler($root, $cachePath))->compile();

    mkdir($root . '/app/acme-fresh', 0775, true);
    file_put_contents(
        $root . '/app/acme-fresh/module.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'acme-fresh', 'enabled' => true];\n",
    );

    $registry = new ModuleRegistry($root, $cachePath);
    $names = array_map(static fn ($module) => $module->name, $registry->enabledModules());

    expect($names)->toContain('acme-fresh')
        ->and($registry->compiledManifestRejectionReason())->toBe('first-party-modules-changed');

    exec('rm -rf ' . escapeshellarg($root));
});

it('reports missing freshness stamps and falls back safely', function (): void {
    $root = runtimeDiagnosticsFixture();
    $cachePath = $root . '/var/cache/modules.php';
    mkdir(dirname($cachePath), 0775, true);
    file_put_contents($cachePath, "<?php\nreturn [];\n");

    $registry = new ModuleRegistry($root, $cachePath);
    expect($registry->enabledModules())->toHaveCount(1)
        ->and($registry->compiledManifestRejectionReason())->toBe('freshness-stamps-missing');

    exec('rm -rf ' . escapeshellarg($root));
});
