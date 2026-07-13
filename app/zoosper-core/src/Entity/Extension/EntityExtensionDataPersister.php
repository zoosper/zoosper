<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Extension;

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * Persists extension table fields from an EntityDataObject.
 *
 * The persister only stores fields declared by FieldDefinitionRegistry as
 * ExtensionTable fields. Rogue submitted values remain ignored.
 */
final readonly class EntityExtensionDataPersister
{
    public function __construct(private EntityExtensionValueRepository $repository)
    {
    }

    public function persist(string $entityType, int|string $entityId, EntityDataObject $data, FieldDefinitionRegistry $registry): void
    {
        foreach ($registry->extensionData($data) as $module => $fields) {
            if (!is_array($fields)) {
                continue;
            }

            foreach ($fields as $fieldName => $value) {
                $this->repository->upsert(new EntityExtensionValue(
                    entityType: $entityType,
                    entityId: $entityId,
                    module: (string) $module,
                    fieldName: (string) $fieldName,
                    value: $value,
                ));
            }
        }
    }
}
