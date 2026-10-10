<?php

declare(strict_types=1);


it('declares every Marko package imported by first-party runtime source', function (): void {
    $root = dirname(__DIR__, 5);
    $namespaceOwners = [
        'Marko\\Admin\\' => 'marko/admin',
        'Marko\\Cache\\' => 'marko/cache',
        'Marko\\Clock\\' => 'marko/clock',
        'Marko\\Config\\' => 'marko/config',
        'Marko\\Core\\' => 'marko/core',
        'Marko\\Encryption\\' => 'marko/encryption',
        'Marko\\Errors\\' => 'marko/errors',
        'Marko\\ErrorsSimple\\' => 'marko/errors-simple',
        'Marko\\Log\\File\\' => 'marko/log-file',
        'Marko\\Log\\' => 'marko/log',
        'Marko\\Pagination\\' => 'marko/pagination',
        'Marko\\Routing\\' => 'marko/routing',
        'Marko\\View\\' => 'marko/view',
    ];
    $failures = [];

    foreach (['app', 'packages'] as $layer) {
        foreach (glob($root . '/' . $layer . '/*', GLOB_ONLYDIR) ?: [] as $packageRoot) {
            $sourceRoot = $packageRoot . '/src';
            $manifestPath = $packageRoot . '/composer.json';
            if (!is_dir($sourceRoot) || !is_file($manifestPath)) {
                continue;
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
            $requirements = array_keys($manifest['require'] ?? []);
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
                $sourceRoot,
                \FilesystemIterator::SKIP_DOTS,
            ));

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                foreach ($namespaceOwners as $prefix => $owner) {
                    $pattern = '/\b(?:use|new|implements|extends|instanceof)\s+' . preg_quote($prefix, '/') . '/';
                    if (preg_match($pattern, $source) === 1 && !in_array($owner, $requirements, true)) {
                        $failures[$manifestPath . ': ' . $owner] = true;
                    }
                }
            }
        }
    }

    expect(array_keys($failures))->toBe([]);
});
