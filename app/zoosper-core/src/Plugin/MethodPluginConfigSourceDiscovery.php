<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Discovers module-owned config/plugins.php files from explicit module roots.
 */
final class MethodPluginConfigSourceDiscovery
{
    /**
     * @param array<string, string>|list<string> $moduleRoots map source => module root, or list of module roots
     * @return list<MethodPluginConfigSource>
     */
    public function discover(array $moduleRoots): array
    {
        $sources = [];

        foreach ($moduleRoots as $source => $moduleRoot) {
            if (!is_string($moduleRoot) || $moduleRoot === '') {
                continue;
            }

            $sourceName = is_string($source) ? $source : basename($moduleRoot);
            $path = rtrim($moduleRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'plugins.php';

            if (is_file($path)) {
                $sources[] = new MethodPluginConfigSource($sourceName, $path);
            }
        }

        return $sources;
    }
}
