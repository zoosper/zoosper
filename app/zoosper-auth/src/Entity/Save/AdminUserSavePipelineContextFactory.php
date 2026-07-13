<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\EntitySaveContext;

/**
 * Creates a save context for AdminUser lifecycle validation/save events.
 */
final readonly class AdminUserSavePipelineContextFactory
{
    public function __construct(private ?AdminUserSaveDataFactory $dataFactory = null)
    {
    }

    /** @param array<string, mixed> $submitted */
    public function create(array $submitted, int|string|null $adminUserId = null): EntitySaveContext
    {
        $factory = $this->dataFactory ?? new AdminUserSaveDataFactory();

        return new EntitySaveContext(
            entityType: 'admin_user',
            data: $factory->fromSubmitted($submitted),
            fieldRegistry: $factory->registry(),
            entityId: $adminUserId,
        );
    }
}
