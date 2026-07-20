<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

/**
 * Result returned after scaffolding a package-based Zoosper module.
 */
final readonly class PackageModuleScaffoldResult
{
    /** @param list<string> $createdFiles */
    public function __construct(
        public string $packageName,
        public string $moduleName,
        public string $namespace,
        public string $packagePath,
        public array $createdFiles,
    ) {
    }
}
