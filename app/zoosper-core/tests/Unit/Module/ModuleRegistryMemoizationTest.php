<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleRegistry;

/*
 * Phase 1.108 behavioural test for ModuleRegistry memoization (Sonnet Phase 2
 * §2.2/§3). Proves the filesystem is scanned only ONCE per registry instance by
 * deleting the module directory between two calls: if enabledModules() were
 * re-scanning, the second call would see zero modules. Uses a self-contained
 * temp directory (created and torn down within the test) rather than a
 * repo-committed fixture, keeping tools/ and tests/ lean.
 */

/** Recursively delete a directory tree. */
function removeDirRecursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeDirRecursive($path) : unlink($path);
    }
    rmdir($dir);
}

/** Create a temp base path with one enabled module under app/<name>/module.php. */
function makeModuleRegistryFixture(string $moduleName, int $sortOrder = 100): string
{
    $base = sys_get_temp_dir() . '/zoosper-module-registry-test-' . bin2hex(random_bytes(8));
    $moduleDir = $base . '/app/' . $moduleName;
    mkdir($moduleDir, 0775, true);

    $php = "<?php\ndeclare(strict_types=1);\nreturn ["
        . "'name' => '" . $moduleName . "', "
        . "'enabled' => true, "
        . "'sort_order' => " . $sortOrder . ", "
        . "];\n";
    file_put_contents($moduleDir . '/module.php', $php);

    return $base;
}

it('discovers an enabled module from a fresh base path', function (): void {
    $base = makeModuleRegistryFixture('demo-module');

    try {
        $registry = new ModuleRegistry($base);
        $modules = $registry->enabledModules();

        expect($modules)->toHaveCount(1)
            ->and($modules[0]->name)->toBe('demo-module')
            ->and($modules[0]->enabled)->toBeTrue();
    } finally {
        removeDirRecursive($base);
    }
});

it('memoizes enabledModules(): a second call does not re-scan the filesystem', function (): void {
    $base = makeModuleRegistryFixture('cached-module');

    try {
        $registry = new ModuleRegistry($base);

        $first = $registry->enabledModules();
        expect($first)->toHaveCount(1);

        // Remove the module directory entirely. If enabledModules() re-scanned,
        // the next call would see zero modules.
        removeDirRecursive($base . '/app/cached-module');

        $second = $registry->enabledModules();

        expect($second)->toHaveCount(1)
            ->and($second[0]->name)->toBe('cached-module')
            ->and($second)->toBe($first);
    } finally {
        removeDirRecursive($base);
    }
});

it('clearCache() forces a genuine re-scan on the next call', function (): void {
    $base = makeModuleRegistryFixture('refreshable-module');

    try {
        $registry = new ModuleRegistry($base);

        $first = $registry->enabledModules();
        expect($first)->toHaveCount(1);

        removeDirRecursive($base . '/app/refreshable-module');
        $registry->clearCache();

        $second = $registry->enabledModules();
        expect($second)->toBe([]);
    } finally {
        removeDirRecursive($base);
    }
});

it('gives each ModuleRegistry instance an independent cache', function (): void {
    $baseA = makeModuleRegistryFixture('module-a');
    $baseB = makeModuleRegistryFixture('module-b');

    try {
        $registryA = new ModuleRegistry($baseA);
        $registryB = new ModuleRegistry($baseB);

        expect($registryA->enabledModules())->toHaveCount(1)
            ->and($registryA->enabledModules()[0]->name)->toBe('module-a')
            ->and($registryB->enabledModules())->toHaveCount(1)
            ->and($registryB->enabledModules()[0]->name)->toBe('module-b');
    } finally {
        removeDirRecursive($baseA);
        removeDirRecursive($baseB);
    }
});

it('orders enabled modules by sort_order then name, unaffected by caching', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-module-registry-order-' . bin2hex(random_bytes(8));
    mkdir($base . '/app/zeta', 0775, true);
    mkdir($base . '/app/alpha', 0775, true);
    file_put_contents($base . '/app/zeta/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name'=>'zeta','enabled'=>true,'sort_order'=>1];\n");
    file_put_contents($base . '/app/alpha/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name'=>'alpha','enabled'=>true,'sort_order'=>1];\n");

    try {
        $registry = new ModuleRegistry($base);
        $names = array_map(static fn ($m) => $m->name, $registry->enabledModules());

        expect($names)->toBe(['alpha', 'zeta']);
    } finally {
        removeDirRecursive($base);
    }
});










