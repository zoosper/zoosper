<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;

/** Renders escaped Marko admin sections for Zoosper's existing admin shell. */
final readonly class AdminNavigationRenderer
{
    /** @param list<AdminSectionInterface> $sections */
    public function render(array $sections, string $active, string $logoutHtml): string
    {
        $html = '<nav class="admin-nav" aria-label="Admin navigation">';

        foreach ($sections as $section) {
            $items = $section->getMenuItems();
            if ($items === []) {
                continue;
            }

            $html .= $this->section($section, $active);
        }

        return $html . $logoutHtml . '</nav>';
    }

    private function section(AdminSectionInterface $section, string $active): string
    {
        $id = $this->escape($section->getId());
        $label = $this->escape($section->getLabel());
        $icon = $this->icon($section->getIcon());
        $html = '<section class="admin-nav-section" data-admin-section="' . $id . '">';
        $html .= '<div class="menu-group">' . $icon . '<span>' . $label . '</span></div>';

        foreach ($section->getMenuItems() as $item) {
            $html .= $this->link($item, $active);
        }

        return $html . '</section>';
    }

    private function link(MenuItemInterface $item, string $active): string
    {
        $id = $this->escape($item->getId());
        $url = $this->escape($item->getUrl());
        $label = $this->escape($item->getLabel());
        $icon = $this->icon($item->getIcon());
        $activeAttributes = $item->getId() === $active ? ' class="active" aria-current="page"' : '';

        return '<a href="' . $url . '" data-admin-item="' . $id . '"' . $activeAttributes . '>' . $icon . '<span>' . $label . '</span></a>';
    }

    private function icon(string $icon): string
    {
        $icon = trim($icon);

        return $icon === ''
            ? ''
            : '<span class="admin-nav-icon" aria-hidden="true" data-admin-icon="' . $this->escape($icon) . '"></span>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
