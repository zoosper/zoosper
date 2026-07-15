<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

/**
 * Result returned after scaffolding a module.
 */
final readonly class ModuleScaffoldResult
{
    /** @param list<string> $createdFiles */
    public function __construct(
        public string $moduleName,
        public string $namespace,
        public string $modulePath,
        public array $createdFiles,
    ) {
    }
}
