<?php

declare(strict_types=1);

/**
 * Validates feature-module boundary rules to prevent forbidden cross-module coupling.
 * Modules should communicate via Core contracts, interfaces, and module discovery
 * rather than tightly coupling to sibling feature modules.
 */

function scanModulePhpFiles(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);
    return $files;
}

it('enforces isolation boundaries for decoupled infrastructure and utility modules', function (): void {
    $root = dirname(__DIR__, 5);

    $boundaryRules = [
        'app/zoosper-site/src' => [
            'Zoosper\Page\',
            'Zoosper\Media\',
            'Zoosper\Theme\',
            'Zoosper\TwoFactor\',
            'Zoosper\Mail\',
        ],
        'app/zoosper-mail/src' => [
            'Zoosper\Page\',
            'Zoosper\Media\',
            'Zoosper\Theme\',
            'Zoosper\TwoFactor\',
            'Zoosper\Site\',
        ],
        'app/zoosper-two-factor/src' => [
            'Zoosper\Page\',
            'Zoosper\Media\',
            'Zoosper\Theme\',
            'Zoosper\Site\',
        ],
    ];

    $violations = [];

    foreach ($boundaryRules as $subPath => $forbiddenNamespaces) {
        $fullPath = $root . '/' . $subPath;
        $files = scanModulePhpFiles($fullPath);

        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            $relativePath = str_replace([$root . '/', $root . '\\'], '', $file);

            foreach ($forbiddenNamespaces as $forbidden) {
                if (str_contains($source, $forbidden)) {
                    $violations[] = sprintf('%s references forbidden namespace %s', $relativePath, $forbidden);
                }
            }
        }
    }

    expect($violations)->toBe(
        [],
        "Feature modules must maintain boundary isolation. Detected violations:\n- " . implode("\n- ", $violations)
    );
});










