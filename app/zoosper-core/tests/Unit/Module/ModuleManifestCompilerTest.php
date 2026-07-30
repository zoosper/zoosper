<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Proves the module-manifest compile/cache foundation (the first piece of
 * the broader "no compiled/cached module discovery" fix both external
 * reviewer passes flagged) is correct and safe:
 *
 * 1. Compiling never lies: the compiled cache produces EXACTLY the same
 *    module set as a live scan of the real repository — the critical
 *    correctness guarantee for anything that replaces a live scan with a
 *    cached read.
 * 2. A fresh ModuleRegistry genuinely reads from the compiled cache (not a
 *    live scan) once one exists — proven by deleting the original module
 *    directories AFTER compiling and confirming the modules are still
 *    found (only possible if the cache, not the filesystem, was read).
 * 3. With no compiled cache present, live discovery still works exactly as
 *    before (the default, backward-compatible behaviour).
 * 4. cache:clear() removes the compiled cache and reverts to live
 *    discovery — proven by adding a NEW module after clearing and
 *    confirming it is now visible (only possible via a fresh live scan).
 * 5. A corrupt/malformed compiled cache file fails safe back to live
 *    discovery rather than fataling the application.
 *
 * File placement: app/zoosper-core/tests/Unit/Module/ModuleManifestCompilerTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function moduleCompilerTestScaffold(): string
{
    $tmp = sys_get_temp_dir() . '/zoosper-module-compiler-' . bin2hex(random_bytes(6));
    mkdir($tmp . '/app/zoosper-fake-a', 0775, true);
    mkdir($tmp . '/app/zoosper-fake-b', 0775, true);

    file_put_contents($tmp . '/app/zoosper-fake-a/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'zoosper-fake-a', 'enabled' => true, 'sort_order' => 10];\n");
    file_put_contents($tmp . '/app/zoosper-fake-b/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'zoosper-fake-b', 'enabled' => true, 'sort_order' => 20];\n");

    return $tmp;
}

it('produces a compiled cache that exactly matches a live scan of the real repository', function (): void {
    $realBasePath = dirname(__DIR__, 5);
    $tempCachePath = sys_get_temp_dir() . '/zoosper-real-module-cache-' . bin2hex(random_bytes(6)) . '.php';

    $liveModules = (new ModuleRegistry($realBasePath))->discoverModulesLive();

    $compiler = new ModuleManifestCompiler($realBasePath, $tempCachePath);
    $compiledModules = $compiler->compile();

    $liveNames = array_map(static fn ($m) => $m->name, $liveModules);
    $compiledNames = array_map(static fn ($m) => $m->name, $compiledModules);

    expect($compiledNames)->toBe($liveNames);
    expect(count($compiledModules))->toBe(count($liveModules));

    // Now prove a FRESH ModuleRegistry reading the compiled cache produces
    // the identical set as well — not just the compiler's own return value.
    $registryFromCache = new ModuleRegistry($realBasePath, $tempCachePath);
    $fromCacheNames = array_map(static fn ($m) => $m->name, $registryFromCache->enabledModules());

    expect($fromCacheNames)->toBe($liveNames);

    @unlink($tempCachePath);
});

it('reads from the compiled cache, not the filesystem, once compiled (proven by removing the source modules afterward)', function (): void {
    $tmp = moduleCompilerTestScaffold();
    $cachePath = $tmp . '/var/cache/modules.php';

    $compiler = new ModuleManifestCompiler($tmp, $cachePath);
    $compiler->compile();

    // Remove the ORIGINAL module directories entirely — if the registry
    // were still live-scanning, it would now find NOTHING.
    exec('rm -rf ' . escapeshellarg($tmp . '/app/zoosper-fake-a'));
    exec('rm -rf ' . escapeshellarg($tmp . '/app/zoosper-fake-b'));

    $registry = new ModuleRegistry($tmp, $cachePath);
    $modules = $registry->enabledModules();
    $names = array_map(static fn ($m) => $m->name, $modules);

    // Still finds both — proves it read the compiled cache, not the
    // (now-empty) filesystem.
    expect($names)->toBe(['zoosper-fake-a', 'zoosper-fake-b']);

    exec('rm -rf ' . escapeshellarg($tmp));
});

it('falls back to live discovery when no compiled cache exists (default, backward-compatible behaviour)', function (): void {
    $tmp = moduleCompilerTestScaffold();
    $nonExistentCachePath = $tmp . '/var/cache/modules.php'; // never compiled

    $registry = new ModuleRegistry($tmp, $nonExistentCachePath);
    $names = array_map(static fn ($m) => $m->name, $registry->enabledModules());

    expect($names)->toBe(['zoosper-fake-a', 'zoosper-fake-b']);

    exec('rm -rf ' . escapeshellarg($tmp));
});

it('reverts to live discovery after cache:clear, picking up a newly added module', function (): void {
    $tmp = moduleCompilerTestScaffold();
    $cachePath = $tmp . '/var/cache/modules.php';

    $compiler = new ModuleManifestCompiler($tmp, $cachePath);
    $compiler->compile();

    // Add a brand-new module AFTER compiling — while the cache is still
    // present, a fresh registry must NOT see it yet (proves the cache is
    // genuinely being used, not silently bypassed).
    mkdir($tmp . '/app/zoosper-fake-c', 0775, true);
    file_put_contents($tmp . '/app/zoosper-fake-c/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'zoosper-fake-c', 'enabled' => true, 'sort_order' => 30];\n");

    $stillCachedNames = array_map(static fn ($m) => $m->name, (new ModuleRegistry($tmp, $cachePath))->enabledModules());
    expect($stillCachedNames)->not->toContain('zoosper-fake-c');

    // Now clear the cache — the new module must become visible again.
    expect($compiler->clear())->toBeTrue();
    expect(is_file($cachePath))->toBeFalse();

    $afterClearNames = array_map(static fn ($m) => $m->name, (new ModuleRegistry($tmp, $cachePath))->enabledModules());
    expect($afterClearNames)->toContain('zoosper-fake-c');

    exec('rm -rf ' . escapeshellarg($tmp));
});

it('fails safe back to live discovery when the compiled cache file is corrupt', function (): void {
    $tmp = moduleCompilerTestScaffold();
    $cachePath = $tmp . '/var/cache/modules.php';

    mkdir(dirname($cachePath), 0775, true);
    file_put_contents($cachePath, "<?php\nthis is not valid PHP syntax {{{\n");

    // Must NOT fatal — must fail safe back to a live scan.
    $names = array_map(static fn ($m) => $m->name, (new ModuleRegistry($tmp, $cachePath))->enabledModules());

    expect($names)->toBe(['zoosper-fake-a', 'zoosper-fake-b']);

    exec('rm -rf ' . escapeshellarg($tmp));
});

it('clear() is safe to call even when nothing has been compiled yet', function (): void {
    $tmp = moduleCompilerTestScaffold();
    $cachePath = $tmp . '/var/cache/modules.php';

    $compiler = new ModuleManifestCompiler($tmp, $cachePath);

    expect($compiler->isCompiled())->toBeFalse();
    expect($compiler->clear())->toBeTrue();

    exec('rm -rf ' . escapeshellarg($tmp));
});
