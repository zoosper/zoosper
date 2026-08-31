<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Admin\Exceptions\AdminException;

final class AdminSectionRegistry implements AdminSectionRegistryInterface
{
    /** @var array<string, AdminSectionInterface> */
    private array $sections = [];

    public function register(AdminSectionInterface $section): void
    {
        $this->sections[$section->getId()] = $section;
    }

    /** @return list<AdminSectionInterface> */
    public function all(): array
    {
        $sections = array_values($this->sections);
        usort($sections, static fn (AdminSectionInterface $a, AdminSectionInterface $b): int =>
            [$a->getSortOrder(), $a->getLabel(), $a->getId()]
            <=> [$b->getSortOrder(), $b->getLabel(), $b->getId()]);

        return $sections;
    }

    public function get(string $id): AdminSectionInterface
    {
        return $this->sections[$id] ?? throw new AdminException(
            sprintf('Admin section "%s" is not registered.', $id),
        );
    }
}










