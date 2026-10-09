<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Closure;
use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Admin\Discovery\AdminSectionDefinition;
use Marko\Admin\Exceptions\AdminException;
use Throwable;

final class AdminSectionRegistry implements AdminSectionRegistryInterface
{
    /** @var array<string, AdminSectionDefinition|AdminSectionInterface> */
    private array $sections = [];

    /** @param null|Closure(class-string): object $resolver */
    public function __construct(
        private readonly ?Closure $resolver = null,
    ) {}

    #[\Override]
    public function register(AdminSectionInterface $section): void
    {
        $this->sections[$section->getId()] = $section;
    }

    #[\Override]
    public function registerDefinition(AdminSectionDefinition $definition): void
    {
        if (!class_exists($definition->className)) {
            throw AdminException::sectionClassNotFound($definition->className);
        }

        if (!is_subclass_of($definition->className, AdminSectionInterface::class)) {
            throw AdminException::sectionMustImplementInterface($definition->className);
        }

        $this->sections[$definition->id] = $definition;
    }

    /** @return list<AdminSectionInterface> */
    #[\Override]
    public function all(): array
    {
        $sections = array_map(
            fn (string $id): AdminSectionInterface => $this->get($id),
            array_keys($this->sections),
        );

        usort(
            $sections,
            static fn (AdminSectionInterface $a, AdminSectionInterface $b): int =>
                [$a->getSortOrder(), $a->getLabel(), $a->getId()]
                <=> [$b->getSortOrder(), $b->getLabel(), $b->getId()],
        );

        return $sections;
    }

    #[\Override]
    public function get(string $id): AdminSectionInterface
    {
        $section = $this->sections[$id] ?? throw AdminException::sectionNotFound($id);

        if ($section instanceof AdminSectionDefinition) {
            $section = $this->build($section);
            $this->sections[$id] = $section;
        }

        return $section;
    }

    private function build(AdminSectionDefinition $definition): AdminSectionInterface
    {
        if ($this->resolver === null) {
            throw new AdminException(
                sprintf('Admin section "%s" cannot be built without a section resolver.', $definition->id),
            );
        }

        /** @var class-string $className */
        $className = $definition->className;

        try {
            $section = ($this->resolver)($className);
        } catch (Throwable $exception) {
            throw AdminException::sectionBuildFailed($definition->id, $definition->className, $exception);
        }

        if (!$section instanceof AdminSectionInterface) {
            throw AdminException::sectionMustImplementInterface($definition->className);
        }

        if ($section->getId() !== $definition->id) {
            throw AdminException::sectionIdMismatch($definition->className, $definition->id, $section->getId());
        }

        return $section;
    }
}
