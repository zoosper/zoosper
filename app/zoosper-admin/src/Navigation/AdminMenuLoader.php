<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Zoosper\Core\Module\ModuleRegistry;

final readonly class AdminMenuLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    /**
     * @return list<AdminMenuItem>
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

            foreach ($config as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $items[] = new AdminMenuItem(
                    code: (string) ($item['code'] ?? $item['id'] ?? ''),
                    label: (string) ($item['label'] ?? $item['title'] ?? ''),
                    url: (string) ($item['url'] ?? '#'),
                    permission: isset($item['permission']) ? (string) $item['permission'] : null,
                    parent: isset($item['parent']) ? (string) $item['parent'] : null,
                    sortOrder: (int) ($item['sort_order'] ?? $item['sortOrder'] ?? 100),
                    group: (string) ($item['group'] ?? 'main'),
                );
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
}
