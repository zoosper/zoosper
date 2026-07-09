<?php

declare(strict_types=1);

namespace Zoosper\Theme\Layout;

use Zoosper\Theme\Theme\Theme;

final readonly class LayoutUpdateRepository
{
    public function forTheme(Theme $theme, string $handle): LayoutUpdate
    {
        $updates = [];
        foreach ($this->candidateFiles($theme) as $file) {
            if (!is_file($file)) {
                continue;
            }
            $config = require $file;
            if (is_array($config)) {
                $updates[] = $config[$handle] ?? [];
            }
        }

        return LayoutUpdate::merge($updates);
    }

    /** @return list<string> */
    private function candidateFiles(Theme $theme): array
    {
        $files = [];

        if ($theme->code !== 'default') {
            $files[] = dirname($theme->path) . '/default/layout.php';
        }

        $files[] = rtrim($theme->path, '/') . '/layout.php';
        return $files;
    }
}
