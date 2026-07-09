<?php

declare(strict_types=1);

namespace Zoosper\Theme\Theme;

final readonly class ThemeRepository
{
    public function __construct(private string $themesPath)
    {
    }

    /** @return list<array<string, string>> */
    public function all(): array
    {
        $themes = [];
        foreach (glob(rtrim($this->themesPath, '/') . '/*/theme.php') ?: [] as $file) {
            $config = require $file;
            if (!is_array($config)) {
                continue;
            }
            $code = (string) ($config['code'] ?? basename(dirname($file)));
            $themes[] = [
                'code' => $code,
                'name' => (string) ($config['name'] ?? $code),
                'version' => (string) ($config['version'] ?? ''),
                'path' => dirname($file),
            ];
        }

        usort($themes, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
        return $themes;
    }

    public function exists(string $code): bool
    {
        foreach ($this->all() as $theme) {
            if ($theme['code'] === $code) {
                return true;
            }
        }
        return false;
    }
}
