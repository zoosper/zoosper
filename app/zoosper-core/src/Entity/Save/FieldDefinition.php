<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Declarative definition of a submitted/admin form field.
 *
 * Field definitions are how core and third-party modules describe what a field
 * means, where it should be stored, and whether it is allowed in the core SQL
 * write map. This keeps controllers clean and prevents unknown POST fields from
 * being blindly written to entity tables.
 */
final readonly class FieldDefinition
{
    public function __construct(
        public string $name,
        public string $label,
        public FieldStorageType $storageType = FieldStorageType::Virtual,
        public ?string $column = null,
        public ?string $module = null,
        public bool $required = false,
        public ?string $normaliserService = null,
        public ?string $validatorService = null,
    ) {
    }

    public static function coreColumn(string $name, string $label, ?string $column = null, bool $required = false): self
    {
        return new self(
            name: $name,
            label: $label,
            storageType: FieldStorageType::CoreColumn,
            column: $column ?? $name,
            required: $required,
        );
    }

    public static function extension(string $module, string $name, string $label): self
    {
        return new self(
            name: $name,
            label: $label,
            storageType: FieldStorageType::ExtensionTable,
            module: $module,
        );
    }

    public static function virtual(string $name, string $label): self
    {
        return new self(name: $name, label: $label, storageType: FieldStorageType::Virtual);
    }
}
