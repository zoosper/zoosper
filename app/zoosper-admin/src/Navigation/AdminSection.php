<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;

final readonly class AdminSection implements AdminSectionInterface
{
    /** @param list<MenuItemInterface> $items */
    public function __construct(
        private string $id,
        private string $label,
        private array $items,
        private string $icon = '',
        private int $sortOrder = 100,
    ) {
    }

    public function getId(): string { return $this->id; }
    public function getLabel(): string { return $this->label; }
    public function getIcon(): string { return $this->icon; }
    public function getSortOrder(): int { return $this->sortOrder; }
    /** @return list<MenuItemInterface> */
    public function getMenuItems(): array { return $this->items; }
}










