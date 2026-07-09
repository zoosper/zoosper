<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

final readonly class ModuleDefinition
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public string $path,
        public bool $enabled = true,
        public array $metadata = [],
    ) {
    }

    public function configPath(string $file): string
    {
        return $this->path . '/config/' . ltrim($file, '/');
    }
}
