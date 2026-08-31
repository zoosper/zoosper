<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleManifestFreshness;
use Zoosper\Core\Module\ModuleRegistry;

function manifestFreshnessFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-manifest-freshness-' . bin2hex(random_bytes(6));
    mkdir($root . '/app/acme-one', 0775, true);
    file_put_contents($root . '/composer.lock', '{"packages":[]}');
    file_put_contents($root . '/app/acme-one/module.php', "<?php\nreturn ['name' => 'acme-one'];\n");

    return $root;
}

it('changes the first-party stamp when an app module is added', function (): void {
    $root = manifestFreshnessFixture();
    $freshness = new ModuleManifestFreshness($root);
    $before = $freshness->firstPartyModulesHash();

    mkdir($root . '/app/acme-two', 0775, true);
    file_put_contents($root . '/app/acme-two/module.php', "<?php\nreturn ['name' => 'acme-two'];\n");

    expect($freshness->firstPartyModulesHash())->not->toBe($before);
    exec('rm -rf ' . escapeshellarg($root));
});

it('rejects a compiled manifest after the first-party module set changes', function (): void {
    $root = manifestFreshnessFixture();
    $cache = $root . '/var/cache/modules.php';
    (new ModuleManifestCompiler($root, $cache))->compile();

    mkdir($root . '/app/acme-two', 0775, true);
    file_put_contents($root . '/app/acme-two/module.php', "<?php\nreturn ['name' => 'acme-two'];\n");

    $names = array_map(static fn ($module): string => $module->name, (new ModuleRegistry($root, $cache))->enabledModules());
    expect($names)->toContain('acme-two');
    exec('rm -rf ' . escapeshellarg($root));
});

it('embeds both freshness stamps and invalidates opcache after replacement', function (): void {
    $root = manifestFreshnessFixture();
    $cache = $root . '/var/cache/modules.php';
    (new ModuleManifestCompiler($root, $cache))->compile();
    $source = file_get_contents($cache);
    $compiler = file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-core/src/Module/ModuleManifestCompiler.php');

    expect($source)->toContain('Composer-Lock-SHA256:');
    expect($source)->toContain('First-Party-Modules-SHA256:');
    expect($compiler)->toContain('opcache_invalidate($this->cachePath, true)');
    exec('rm -rf ' . escapeshellarg($root));
});










