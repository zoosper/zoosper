<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Runtime registry of field definitions for an entity/form save operation.
 *
 * Modules should contribute field definitions instead of making controllers or
 * repositories know about every possible submitted field. The registry can then
 * produce safe write maps for repositories while keeping extension values
 * available to observers and module-specific persistence handlers.
 */
final class FieldDefinitionRegistry
{
    /** @var array<string, FieldDefinition> */
    private array $definitions = [];

    public function register(FieldDefinition $definition): self
    {
        $this->definitions[$definition->name] = $definition;

        return $this;
    }

    /** @param iterable<FieldDefinition> $definitions */
    public function registerMany(iterable $definitions): self
    {
        foreach ($definitions as $definition) {
            $this->register($definition);
        }

        return $this;
    }

    public function get(string $name): ?FieldDefinition
    {
        return $this->definitions[$name] ?? null;
    }

    /** @return array<string, FieldDefinition> */
    public function all(): array
    {
        return $this->definitions;
    }

    /** @return array<string, string> field name => column name */
    public function coreColumnWriteMap(): array
    {
        $map = [];
        foreach ($this->definitions as $name => $definition) {
            if ($definition->storageType === FieldStorageType::CoreColumn && $definition->column !== null) {
                $map[$name] = $definition->column;
            }
        }

        return $map;
    }

    /** @return array<string, mixed> column name => value */
    public function coreColumnData(EntityDataObject $data): array
    {
        $mapped = [];
        foreach ($this->coreColumnWriteMap() as $field => $column) {
            if ($data->hasData($field)) {
                $mapped[$column] = $data->getData($field);
            }
        }

        return $mapped;
    }

    /** @return array<string, mixed> */
    public function extensionData(EntityDataObject $data): array
    {
        $extensionData = [];
        foreach ($this->definitions as $definition) {
            if ($definition->storageType !== FieldStorageType::ExtensionTable || $definition->module === null) {
                continue;
            }

            if ($data->hasData($definition->name)) {
                $extensionData[$definition->module][$definition->name] = $data->getData($definition->name);
            }
        }

        return $extensionData;
    }
}
