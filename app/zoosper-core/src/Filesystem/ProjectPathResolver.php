<?php

declare(strict_types=1);

namespace Zoosper\Core\Filesystem;

/**
 * Resolves project paths safely so runtime/generated files do not drift into
 * public/ because of PHP's current working directory.
 */
final readonly class ProjectPathResolver
{
    public function __construct(private string $basePath)
    {
    }

    public static function fromCoreModule(): self
    {
        return new self(dirname(__DIR__, 4));
    }

    public function basePath(string $path = ''): string
    {
        return $this->join($this->basePath, $path);
    }

    public function varPath(string $path = ''): string
    {
        return $this->join($this->basePath . '/var', $path);
    }

    public function storagePath(string $path = ''): string
    {
        return $this->join($this->basePath . '/storage', $path);
    }

    public function publicPath(string $path = ''): string
    {
        return $this->join($this->basePath . '/public', $path);
    }

    public function configPath(string $path = ''): string
    {
        return $this->join($this->basePath . '/config', $path);
    }

    public function absolute(string $path): string
    {
        if ($path === '') {
            return $this->basePath;
        }

        if (str_starts_with($path, '/')) {
            return rtrim($path, '/');
        }

        return $this->basePath($path);
    }

    private function join(string $root, string $path): string
    {
        $root = rtrim($root, '/');
        $path = trim($path, '/');

        return $path === '' ? $root : $root . '/' . $path;
    }
}
