<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;

/** Renders escaped Marko admin sections and allowlisted semantic icons. */
final readonly class AdminNavigationRenderer
{
    /** @var array<string, string> */
    private const array ICONS = [
        'access-tokens' => '<circle cx="12" cy="8" r="3"/><path d="M9.5 10.5 4 16v4h4v-2h2v-2h2l2.5-2.5"/>',
        'audit-log' => '<path d="M9 5h10M9 12h10M9 19h10"/><path d="m3.5 5 .8.8L6 4m-2.5 8 .8.8L6 11m-2.5 8 .8.8L6 18"/>',
        'content' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'design' => '<path d="m12 3 3 3-8.5 8.5L3 15l.5-3.5z"/><path d="m14 5 3-2 4 4-2 3m-8 7 2 4 2-4 4-2-4-2-2-4-2 4-4 2z"/>',
        'file-text' => '<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h6"/>',
        'home' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v11h14V10M9 21v-7h6v7"/>',
        'login-history' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2M3 4v5h5"/>',
        'logout' => '<path d="M10 4H5v16h5M14 8l4 4-4 4M8 12h10"/>',
        'media' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m4 17 5-5 4 4 2-2 5 5"/>',
        'menus' => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
        'orders' => '<path d="M6 3h12l2 5-2 13H6L4 8z"/><path d="M4 8h16M9 12v5M15 12v5"/>',
        'pages' => '<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/>',
        'roles' => '<path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6z"/><path d="M9 12h6M12 9v6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3A1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1z"/>',
        'site-domains' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'sites' => '<path d="M4 21V5l8-3 8 3v16M8 8h1m6 0h1M8 12h1m6 0h1M8 16h1m6 0h1M10 21v-4h4v4"/>',
        'themes' => '<path d="M12 3a9 9 0 1 0 0 18h1.5a1.5 1.5 0 0 0 0-3H12a2 2 0 0 1 0-4h3a6 6 0 0 0 0-12z"/><circle cx="7.5" cy="10" r="1"/><circle cx="10" cy="6.5" r="1"/><circle cx="15" cy="6.5" r="1"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'fallback' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="2"/>',
    ];

    /** @param list<AdminSectionInterface> $sections */
    public function render(array $sections, string $active, string $logoutHtml): string
    {
        $html = '<nav class="admin-nav" aria-label="Admin navigation">';

        foreach ($sections as $section) {
            if ($section->getMenuItems() === []) {
                continue;
            }

            $html .= $this->section($section, $active);
        }

        return $html . $logoutHtml . '</nav>';
    }

    public function renderIcon(string $icon): string
    {
        $identifier = trim($icon);
        $paths = self::ICONS[$identifier] ?? self::ICONS['fallback'];
        $metadata = $identifier !== '' ? $identifier : 'fallback';

        return '<span class="admin-nav-icon" aria-hidden="true" data-admin-icon="' . $this->escape($metadata) . '">'
            . '<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">' . $paths . '</svg></span>';
    }

    private function section(AdminSectionInterface $section, string $active): string
    {
        $id = $this->escape($section->getId());
        $label = $this->escape($section->getLabel());
        $html = '<section class="admin-nav-section" data-admin-section="' . $id . '">';
        $html .= '<h2 class="menu-group"><span>' . $label . '</span></h2>';

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
        $activeAttributes = $item->getId() === $active ? ' class="active" aria-current="page"' : '';

        return '<a href="' . $url . '" data-admin-item="' . $id . '" data-admin-label="' . $label
            . '" title="' . $label . '"' . $activeAttributes . '>' . $this->renderIcon($item->getIcon())
            . '<span>' . $label . '</span></a>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
