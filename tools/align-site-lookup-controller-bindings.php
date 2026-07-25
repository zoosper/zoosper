<?php

declare(strict_types=1);

/**
 * Phase 1.91 surgical alignment of controller site-lookup bindings.
 *
 * Dry-run by default. Pass --apply to write changes.
 */

$root = dirname(__DIR__);
$appDir = $root . '/app';
$apply = in_array('--apply', $argv, true);
$mode = $apply ? 'apply' : 'dry-run';

$wrongImport = 'use Zoosper\\Site\\Repository\\SiteLookupInterface;';
$correctImport = 'use Zoosper\\Core\\Site\\SiteLookupInterface;';
$interfaceFqcn = '\\Zoosper\\Core\\Site\\SiteLookupInterface';

$controllerImportChanges = [];
$configArgumentChanges = [];
$controllersNeedingInterface = [];
$warnings = [];

$phpFiles = static function (string $dir): Generator {
    if (!is_dir($dir)) {
        return;
    }

    $it = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            static function (SplFileInfo $f): bool {
                $p = str_replace('\\', '/', $f->getPathname());
                if ($f->isDir()) {
                    return !preg_match('#/(vendor|node_modules|var|cache|generated|storage)$#', $p);
                }
                return $f->getExtension() === 'php';
            }
        )
    );

    foreach ($it as $f) {
        if ($f instanceof SplFileInfo && $f->isFile()) {
            yield str_replace('\\', '/', $f->getPathname());
        }
    }
};

foreach ($phpFiles($appDir) as $path) {
    if (!str_contains($path, '/Controller/') && !str_contains(basename($path), 'Controller')) {
        continue;
    }

    $src = (string) file_get_contents($path);
    if (preg_match('/\bSiteLookupInterface\s+\$[A-Za-z_]\w*/', $src) !== 1) {
        continue;
    }

    $shortName = basename($path, '.php');
    $controllersNeedingInterface[$shortName] = true;

    if (str_contains($src, $wrongImport)) {
        $updated = str_replace($wrongImport, $correctImport, $src);
        if ($updated !== $src) {
            $controllerImportChanges[] = [
                'file' => ltrim(str_replace($root, '', $path), '/'),
                'from' => $wrongImport,
                'to' => $correctImport,
            ];
            if ($apply) {
                file_put_contents($path, $updated);
            }
        }
    }
}

$sitesArgPattern = '/sites:\s*\$services->get\(\s*\\\\?(?:Zoosper\\\\Site\\\\Repository\\\\)?SiteRepository::class\s*\)/';

foreach ($phpFiles($appDir) as $path) {
    if (!preg_match('#/config/controllers\.php$#', $path)) {
        continue;
    }

    $src = (string) file_get_contents($path);
    if (preg_match_all('/([A-Za-z_]\w*Controller)::class\s*=>/', $src, $m) < 1) {
        continue;
    }

    $definesNeedy = false;
    foreach ($m[1] as $shortName) {
        if (isset($controllersNeedingInterface[$shortName])) {
            $definesNeedy = true;
            break;
        }
    }
    if (!$definesNeedy) {
        continue;
    }

    if (preg_match($sitesArgPattern, $src) !== 1) {
        $warnings[] = ltrim(str_replace($root, '', $path), '/')
            . ': defines an interface-consuming controller but the sites: argument did not match the expected concrete pattern; review manually.';
        continue;
    }

    $updated = preg_replace(
        $sitesArgPattern,
        'sites: $services->get(' . $interfaceFqcn . '::class)',
        $src
    );

    if ($updated !== null && $updated !== $src) {
        $configArgumentChanges[] = [
            'file' => ltrim(str_replace($root, '', $path), '/'),
            'to' => 'sites: $services->get(' . $interfaceFqcn . '::class)',
        ];
        if ($apply) {
            file_put_contents($path, $updated);
        }
    }
}

$docsReportDir = $root . '/docs/reports';
if (!is_dir($docsReportDir) && !mkdir($docsReportDir, 0775, true) && !is_dir($docsReportDir)) {
    fwrite(STDERR, "Unable to create reports directory.\n");
    exit(1);
}

$generatedAt = gmdate('c');
$report = [
    'phase' => '1.91-site-lookup-controller-binding-alignment',
    'generatedAt' => $generatedAt,
    'mode' => $mode,
    'controllersNeedingInterface' => array_keys($controllersNeedingInterface),
    'controllerImportChanges' => $controllerImportChanges,
    'configArgumentChanges' => $configArgumentChanges,
    'warnings' => $warnings,
];
file_put_contents(
    $docsReportDir . '/site-lookup-controller-binding-alignment.json',
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

echo "## Site Lookup Controller Binding Alignment\n\n";
echo "Generated: {$generatedAt}\n";
echo "Mode: {$mode}\n";
echo 'Controllers needing interface: ' . count($controllersNeedingInterface) . "\n";
echo 'Controller import fixes: ' . count($controllerImportChanges) . "\n";
echo 'Config sites: argument fixes: ' . count($configArgumentChanges) . "\n";
echo 'Warnings: ' . count($warnings) . "\n";
echo "Report: docs/reports/site-lookup-controller-binding-alignment.json\n\n";

if ($controllersNeedingInterface !== []) {
    echo "### Controllers depending on SiteLookupInterface\n";
    foreach (array_keys($controllersNeedingInterface) as $name) {
        echo "- {$name}\n";
    }
    echo "\n";
}

if ($controllerImportChanges !== []) {
    echo $apply ? "### Import lines fixed\n" : "### Import lines to fix\n";
    foreach ($controllerImportChanges as $c) {
        echo "- {$c['file']}\n";
    }
    echo "\n";
}

if ($configArgumentChanges !== []) {
    echo $apply ? "### Config sites: arguments fixed\n" : "### Config sites: arguments to fix\n";
    foreach ($configArgumentChanges as $c) {
        echo "- {$c['file']}\n";
    }
    echo "\n";
}

if ($warnings !== []) {
    echo "### Warnings (manual review)\n";
    foreach ($warnings as $w) {
        echo "- {$w}\n";
    }
    echo "\n";
}

if (!$apply) {
    echo "Dry-run only. Re-run with --apply to write these changes.\n";
}
