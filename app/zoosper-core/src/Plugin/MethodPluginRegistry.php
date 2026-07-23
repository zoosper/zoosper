<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * In-memory registry for method plugin definitions.
 */
final class MethodPluginRegistry
{
    /** @var array<string, list<MethodPluginDefinition>> */
    private array $definitionsByMethod = [];

    /**
     * @param list<MethodPluginDefinition> $definitions
     */
    public function __construct(array $definitions = [])
    {
        foreach ($definitions as $definition) {
            $this->add($definition);
        }
    }

    public function add(MethodPluginDefinition $definition): void
    {
        if (!$definition->enabled) {
            return;
        }

        $this->definitionsByMethod[$definition->key()][] = $definition;
        usort(
            $this->definitionsByMethod[$definition->key()],
            static fn (MethodPluginDefinition $a, MethodPluginDefinition $b): int => $a->sortOrder <=> $b->sortOrder
        );
    }

    /**
     * @return list<MethodPluginDefinition>
     */
    public function for(string $subject, string $method): array
    {
        return $this->definitionsByMethod[$subject . '::' . $method] ?? [];
    }
}
