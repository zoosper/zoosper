<?php

declare(strict_types=1);

namespace Zoosper\Core\Config;

use Zoosper\Core\Module\ModuleRegistry;

/**
 * Builds the application configuration through one shared HTTP/CLI path.
 *
 * Module defaults are layered below project-root overrides by
 * ModuleConfigAggregator. The returned compatibility repository remains in
 * place while consumers migrate to Marko's configuration contract.
 */
final readonly class ApplicationConfigLoader
{
    private ModuleRegistry $modules;

    public function __construct(
        private string $basePath,
        ?ModuleRegistry $modules = null,
    ) {
        $this->modules = $modules ?? new ModuleRegistry($basePath);
    }

    public function load(): ConfigRepository
    {
        return ConfigRepository::fromArray(
            (new ModuleConfigAggregator(
                $this->modules,
                rtrim($this->basePath, '/\\') . '/config',
            ))->aggregate(),
        );
    }
}










