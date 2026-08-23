<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use Marko\Admin\Contracts\MenuItemInterface;

/** Builds Dashboard shortcuts from an already permission-filtered Admin menu. */
final readonly class DashboardQuickLinks
{
    /**
     * @param list<MenuItemInterface> $items
     * @return list<array{code: string, label: string, url: string, icon: string}>
     */
    public function fromMenuItems(array $items): array
    {
        $links = [];
        $seenUrls = [];

        foreach ($items as $item) {
            $code = trim($item->getId());
            $label = trim($item->getLabel());
            $url = trim($item->getUrl());

            if ($code === 'dashboard' || $label === '' || $url === '' || $url === '#') {
                continue;
            }

            if (isset($seenUrls[$url])) {
                continue;
            }

            $seenUrls[$url] = true;
            $links[] = [
                'code' => $code,
                'label' => $label,
                'url' => $url,
                'icon' => trim($item->getIcon()),
            ];
        }

        return $links;
    }
}
