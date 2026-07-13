<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;

/**
 * High-level AdminUser save-pipeline facade.
 *
 * UserAdminController can migrate to this facade in the next phase with minimal
 * controller logic. The facade builds the save data object, save context and
 * SQL-safe core write statement without exposing controllers to the internals
 * of field definitions and write maps.
 */
final readonly class AdminUserSavePipeline
{
    public function __construct(
        private ?AdminUserSaveDataFactory $dataFactory = null,
        private ?AdminUserSavePipelineContextFactory $contextFactory = null,
        private ?AdminUserCoreWriteSqlBuilder $sqlBuilder = null,
    ) {
    }

    /** @param array<string, mixed> $submitted */
    public function data(array $submitted): EntityDataObject
    {
        return ($this->dataFactory ?? new AdminUserSaveDataFactory())->fromSubmitted($submitted);
    }

    /** @param array<string, mixed> $submitted */
    public function context(array $submitted, int|string|null $adminUserId = null): EntitySaveContext
    {
        return ($this->contextFactory ?? new AdminUserSavePipelineContextFactory())->create($submitted, $adminUserId);
    }

    /**
     * @param array<string, mixed> $submitted
     * @return array{sql: string, params: array<string, mixed>}
     */
    public function updateSql(int $id, array $submitted): array
    {
        return ($this->sqlBuilder ?? new AdminUserCoreWriteSqlBuilder())->buildUpdate($id, $this->data($submitted));
    }
}
