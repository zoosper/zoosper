<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/**
 * Immutable metadata for one discovered Zoosper module.
 */
final readonly class Module
{
    public function __construct(
        public string $name,
        public string $path,
        public bool $enabled = true,
        public string $version = '0.1.0',
        public int $sortOrder = 100,
        public string $source = 'app',
    ) {
    }

    public function configPath(string $file): string
    {
        return rtrim($this->path, '/\\') . '/config/' . ltrim($file, '/\\');
    }

    public function resourcePath(string $path = ''): string
    {
        return rtrim($this->path, '/\\') . '/resources' . ($path !== '' ? '/' . ltrim($path, '/\\') : '');
    }

    public function moduleFile(): string
    {
        return rtrim($this->path, '/\\') . '/module.php';
    }
}
