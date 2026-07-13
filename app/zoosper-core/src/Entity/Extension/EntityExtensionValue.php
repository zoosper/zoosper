<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Extension;

/**
 * Represents one module-owned extension field value for an entity.
 *
 * Extension values keep third-party module fields out of core entity tables
 * while still allowing modules to persist their own form data safely.
 */
final readonly class EntityExtensionValue
{
    public function __construct(
        public string $entityType,
        public int|string $entityId,
        public string $module,
        public string $fieldName,
        public mixed $value,
    ) {
    }
}
