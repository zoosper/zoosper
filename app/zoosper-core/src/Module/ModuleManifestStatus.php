<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/** Provides a read-only operational view of the compiled module manifest. */
final readonly class ModuleManifestStatus
{
    private string $cachePath;

    public function __construct(private string $basePath, ?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? rtrim($basePath, '/\\') . '/var/cache/modules.php';
    }

    /**
     * @return array{
     *     status: 'missing'|'fresh'|'rejected',
     *     cachePath: string,
     *     moduleCount: int,
     *     rejectionReason: string|null
     * }
     */
    public function inspect(): array
    {
        $manifestExists = is_file($this->cachePath);
        $registry = new ModuleRegistry($this->basePath, $this->cachePath);
        $modules = $registry->enabledModules();
        $rejectionReason = $registry->compiledManifestRejectionReason();

        return [
            'status' => !$manifestExists
                ? 'missing'
                : ($rejectionReason === null ? 'fresh' : 'rejected'),
            'cachePath' => $this->cachePath,
            'moduleCount' => count($modules),
            'rejectionReason' => $rejectionReason,
        ];
    }
}










