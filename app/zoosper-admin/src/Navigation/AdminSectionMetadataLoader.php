<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Zoosper\Core\Module\ModuleRegistry;

/** Loads mergeable module-owned admin section metadata. Later modules replace IDs. */
final readonly class AdminSectionMetadataLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    /** @return array<string, AdminSectionMetadata> */
    public function load(): array
    {
        $metadata = [];
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_sections.php');
            if (!is_file($file)) {
                continue;
            }

            $declarations = require $file;
            if (!is_array($declarations)) {
                continue;
            }

            foreach ($declarations as $declaration) {
                if (!is_array($declaration)) {
                    continue;
                }

                $id = $this->normaliseId((string) ($declaration['id'] ?? ''));
                $label = trim((string) ($declaration['label'] ?? ''));
                if ($id === '' || $label === '') {
                    continue;
                }

                $metadata[$id] = new AdminSectionMetadata(
                    $id,
                    $label,
                    trim((string) ($declaration['icon'] ?? '')),
                    (int) ($declaration['sort_order'] ?? $declaration['sortOrder'] ?? 100),
                );
            }
        }

        return $metadata;
    }

    public function normaliseId(string $value): string
    {
        return trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', trim($value))), '-');
    }
}










