<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Context object passed through admin entity save lifecycle events.
 */
final class EntitySaveContext
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function __construct(
        private readonly string $entityType,
        private readonly EntityDataObject $data,
        private readonly FieldDefinitionRegistry $fieldRegistry,
        private readonly int|string|null $entityId = null,
    ) {
    }

    public function entityType(): string
    {
        return $this->entityType;
    }

    public function entityId(): int|string|null
    {
        return $this->entityId;
    }

    public function data(): EntityDataObject
    {
        return $this->data;
    }

    public function fieldRegistry(): FieldDefinitionRegistry
    {
        return $this->fieldRegistry;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
