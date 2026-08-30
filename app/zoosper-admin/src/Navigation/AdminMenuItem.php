<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\MenuItemInterface;

/**
 * Zoosper-compatible admin navigation item implementing Marko's standard
 * admin-menu contract while preserving existing public properties and ACL API.
 */
final readonly class AdminMenuItem implements MenuItemInterface
{
    /**
     * @param list<AdminMenuItem|MenuItemInterface> $children
     */
    public function __construct(
        public string $code,
        public string $label,
        public string $url,
        public ?string $permission = null,
        public ?string $parent = null,
        public int $sortOrder = 100,
        public string $group = 'main',
        public string $icon = '',
        public array $children = [],
    ) {
    }

    public function getId(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getPermission(): string
    {
        return $this->permission ?? '';
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return list<AdminMenuItem|MenuItemInterface>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    /**
     * @param list<AdminMenuItem|MenuItemInterface> $children
     */
    public function withChildren(array $children): self
    {
        return new self(
            code: $this->code,
            label: $this->label,
            url: $this->url,
            permission: $this->permission,
            parent: $this->parent,
            sortOrder: $this->sortOrder,
            group: $this->group,
            icon: $this->icon,
            children: array_values($children),
        );
    }

    public function isAllowed(callable $permissionChecker): bool
    {
        return $this->permission === null || $permissionChecker($this->permission);
    }
}
