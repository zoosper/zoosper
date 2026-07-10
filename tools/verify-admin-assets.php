<?php

declare(strict_types=1);

/**
 * Verify module-owned admin asset discovery.
 *
 * This read-only tool prints discovered stylesheet/script asset paths. It must
 * never print runtime secrets; asset declarations are static config only.
 */

$basePath = dirname(__DIR__);

if (!function_exists('env')) {
    /**
     * Return an environment value with a fallback default for standalone tools.
     */
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false && $value !== '' ? $value : $default;
    }
}

require $basePath . '/vendor/autoload.php';

$modules = new \Zoosper\Core\Module\ModuleRegistry($basePath);
$registry = new \Zoosper\Admin\Asset\AdminAssetRegistry($modules);

print "Zoosper admin asset discovery\n";
print "============================\n\n";

print "Stylesheets:\n";
foreach ($registry->stylesheets() as $asset) {
    print '- ' . $asset->handle . ' => ' . $asset->path . PHP_EOL;
}

print "\nScripts:\n";
foreach ($registry->scripts() as $asset) {
    print '- ' . $asset->handle . ' => ' . $asset->path . ($asset->defer ? ' defer' : '') . PHP_EOL;
}
