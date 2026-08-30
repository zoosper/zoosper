<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\MenuItemInterface;

use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Url\AdminPathCollectionTransformer;

final readonly class AdminMenuLoader
{
    public function __construct(
        private ModuleRegistry $modules,
        private ?AdminPathCollectionTransformer $adminPaths = null,
    ) {
    }

    /**
     * @return list<MenuItemInterface>
     */
    public function load(): array
    {
        $items = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_menu.php');

            if (!is_file($file)) {
                continue;
            }

            $config = require $file;

            if (!is_array($config)) {
                continue;
            }

            $declarations = $this->adminPaths?->menu($config) ?? $config;

            foreach ($this->parseDeclarations($declarations) as $menuItem) {
                $items[] = $menuItem;
            }
        }

        $items = array_filter(
            $items,
            static fn (AdminMenuItem $item): bool => $item->code !== '' && $item->label !== '',
        );

        usort(
            $items,
            static fn (AdminMenuItem $a, AdminMenuItem $b): int => [$a->group, $a->sortOrder, $a->label] <=> [$b->group, $b->sortOrder, $b->label],
        );

        return array_values($items);
    }

    /**
     * @param array<array-key, mixed> $declarations
     * @return list<AdminMenuItem>
     */
    private function parseDeclarations(array $declarations, string $defaultGroup = 'main', ?string $parentCode = null): array
    {
        $items = [];

        foreach ($declarations as $item) {
            if (!is_array($item)) {
                continue;
            }

            $code = (string) ($item['code'] ?? $item['id'] ?? '');
            $label = (string) ($item['label'] ?? $item['title'] ?? '');
            $group = (string) ($item['group'] ?? $defaultGroup);
            $parent = isset($item['parent']) ? (string) $item['parent'] : $parentCode;

            $items[] = new AdminMenuItem(
                code: $code,
                label: $label,
                url: (string) ($item['url'] ?? '#'),
                permission: isset($item['permission']) ? (string) $item['permission'] : null,
                parent: $parent,
                sortOrder: (int) ($item['sort_order'] ?? $item['sortOrder'] ?? 100),
                group: $group,
                icon: (string) ($item['icon'] ?? ''),
            );

            if (isset($item['children']) && is_array($item['children']) && $code !== '') {
                foreach ($this->parseDeclarations($item['children'], $group, $code) as $child) {
                    $items[] = $child;
                }
            }
        }

        return $items;
    }
}
