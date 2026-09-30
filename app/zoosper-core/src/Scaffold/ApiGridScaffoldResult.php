<?php
declare(strict_types=1);
namespace Zoosper\Core\Scaffold;
/**
 * Result returned after scaffolding an API-backed Grid package.
 *
 * @psalm-api
 */
final readonly class ApiGridScaffoldResult
{
    /** @param list<string> $createdFiles */
    public function __construct(
        public string $packageName,
        public string $moduleName,
        public string $namespace,
        public string $packagePath,
        public string $gridKey,
        public string $route,
        public array $createdFiles,
    ) {}
}
