<?php

declare(strict_types=1);

namespace Zoosper\Theme\Theme;

use RuntimeException;

final readonly class ThemeResolver
{
    public function __construct(private string $themesPath, private string $defaultTheme = 'default')
    {
    }

    public function resolve(?string $themeCode = null): Theme
    {
        $code = $themeCode !== null && trim($themeCode) !== '' ? trim($themeCode) : $this->defaultTheme;
        $path = rtrim($this->themesPath, '/') . '/' . $code;

        if (!is_dir($path)) {
            throw new RuntimeException('Theme does not exist: ' . $code);
        }

        return new Theme($code, $path);
    }
}
