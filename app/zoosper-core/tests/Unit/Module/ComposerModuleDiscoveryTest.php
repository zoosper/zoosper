<?php

declare(strict_types=1);

use Zoosper\Core\Module\ComposerModuleDiscovery;

test('composer discovery supports both installed formats and ignores malformed entries', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-composer-discovery-' . bin2hex(random_bytes(6));
    mkdir($root . '/vendor/composer', 0775, true);
    mkdir($root . '/vendor/acme/example', 0775, true);
    $module = $root . '/vendor/acme/example/custom.php';
    $installed = $root . '/vendor/composer/installed.json';
    file_put_contents($module, '<?php return [];');

    try {
        $package = [
            'name' => 'acme/example',
            'type' => 'zoosper-module',
            'install-path' => '../acme/example',
            'extra' => ['zoosper' => ['module' => ' custom.php ']],
        ];
        $discovery = new ComposerModuleDiscovery($root);
        foreach ([$package, ['packages' => [$package, null, $package]]] as $fixture) {
            $data = isset($fixture['packages']) ? $fixture : [$fixture, null, $fixture];
            file_put_contents($installed, json_encode($data, JSON_THROW_ON_ERROR));
            expect($discovery->moduleFiles())->toBe([$module]);
        }

        file_put_contents($installed, '{invalid');
        expect($discovery->moduleFiles())->toBe([]);
        file_put_contents($installed, json_encode(['packages' => [null, ['type' => 'library']]], JSON_THROW_ON_ERROR));
        expect($discovery->moduleFiles())->toBe([]);
    } finally {
        unlink($installed);
        unlink($module);
        rmdir($root . '/vendor/acme/example');
        rmdir($root . '/vendor/acme');
        rmdir($root . '/vendor/composer');
        rmdir($root . '/vendor');
        rmdir($root);
    }
});