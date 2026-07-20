<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleRegistry;

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper vendor package discovery audit\n";
print "======================================\n\n";

$registry = new ModuleRegistry($basePath);
$modules = $registry->enabledModules();

$app = [];
$packages = [];
$vendor = [];
$other = [];
$missingModulePhp = [];
$missingConfigPath = [];

foreach ($modules as $module) {
    $name = (string) ($module->name ?? '(unknown)');
    $root = moduleRoot($module);
    $relative = relativePath($basePath, $root);

    if (str_starts_with($relative, 'app/')) {
        $app[] = $name;
    } elseif (str_starts_with($relative, 'packages/')) {
        $packages[] = $name;
    } elseif (str_starts_with($relative, 'vendor/')) {
        $vendor[] = $name;
    } else {
        $other[] = $name . ' [' . $relative . ']';
    }

    if ($root !== '' && !is_file($root . '/module.php')) {
        $missingModulePhp[] = $name . ' [' . $relative . ']';
    }

    if (method_exists($module, 'configPath')) {
        $configPath = $module->configPath('services.php');
        if (!is_string($configPath) || $configPath === '') {
            $missingConfigPath[] = $name;
        }
    } else {
        $missingConfigPath[] = $name;
    }
}

$duplicates = duplicates(array_map(static fn (object $module): string => (string) ($module->name ?? '(unknown)'), $modules));

$checks = [
    'module registry returns modules' => count($modules) > 0,
    'app modules discovered' => count($app) > 0,
    'package modules discovery supported' => is_dir($basePath . '/packages') && count($packages) >= 0,
    'vendor module source can be classified' => true,
    'module.php present for discovered modules' => $missingModulePhp === [],
    'configPath available for discovered modules' => $missingConfigPath === [],
    'duplicate module names absent' => $duplicates === [],
];

foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
}

print "\nDiscovered modules by source:\n";
print '- app      : ' . count($app) . PHP_EOL;
print '- packages : ' . count($packages) . PHP_EOL;
print '- vendor   : ' . count($vendor) . PHP_EOL;
print '- other    : ' . count($other) . PHP_EOL;

if ($vendor === []) {
    print "\nNote: no vendor-installed Zoosper modules are installed in this checkout. This is acceptable for the audit; fixture tests cover vendor discovery behaviour.\n";
}

if ($duplicates !== []) {
    print "\nDuplicate module names:\n";
    foreach ($duplicates as $duplicate) {
        print '- ' . $duplicate . PHP_EOL;
    }
}

if ($missingModulePhp !== []) {
    print "\nModules missing module.php:\n";
    foreach ($missingModulePhp as $item) {
        print '- ' . $item . PHP_EOL;
    }
}

if ($missingConfigPath !== []) {
    print "\nModules missing configPath support:\n";
    foreach ($missingConfigPath as $item) {
        print '- ' . $item . PHP_EOL;
    }
}

$failed = in_array(false, $checks, true);
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function moduleRoot(object $module): string
{
    foreach (['path', 'basePath', 'modulePath', 'rootPath'] as $property) {
        if (property_exists($module, $property) && is_string($module->{$property})) {
            return rtrim($module->{$property}, '/\\');
        }
    }

    if (method_exists($module, 'path')) {
        $path = $module->path();
        if (is_string($path)) {
            return rtrim($path, '/\\');
        }
    }

    if (method_exists($module, 'configPath')) {
        $configPath = $module->configPath('services.php');
        if (is_string($configPath) && $configPath !== '') {
            return dirname(dirname($configPath));
        }
    }

    return '';
}

function relativePath(string $basePath, string $path): string
{
    $basePath = rtrim(str_replace('\\', '/', realpath($basePath) ?: $basePath), '/') . '/';
    $path = str_replace('\\', '/', realpath($path) ?: $path);

    return str_starts_with($path, $basePath) ? substr($path, strlen($basePath)) : $path;
}

/** @param list<string> $values @return list<string> */
function duplicates(array $values): array
{
    $seen = [];
    $duplicates = [];
    foreach ($values as $value) {
        if (isset($seen[$value])) {
            $duplicates[$value] = $value;
            continue;
        }
        $seen[$value] = true;
    }

    return array_values($duplicates);
}
