<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Normalises page momentum menu metadata for a future admin menu integration.
 *
 * This provider does not register menu items. It only returns definitions when
 * the supplied metadata is explicitly enabled.
 */
final class PageMomentumMenuDefinitionProvider
{
    /**
     * @param array<string, mixed> $config
     * @return list<array<string, mixed>>
     */
    public function items(array $config): array
    {
        $root = $config['page_momentum_menu'] ?? [];
        if (!is_array($root) || ($root['enabled'] ?? false) !== true) {
            return [];
        }

        $items = $root['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(
            $items,
            static fn (mixed $item): bool => is_array($item)
                && isset($item['label'], $item['route'], $item['permission'])
        ));
    }
}
