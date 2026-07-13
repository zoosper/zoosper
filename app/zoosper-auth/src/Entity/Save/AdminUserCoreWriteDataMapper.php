<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * Maps AdminUser save data to safe admin_users core-column data.
 *
 * This class is the bridge between flexible form/entity data and SQL-safe core
 * persistence. It never writes raw submitted keys directly; it only returns
 * values declared by the field registry as CoreColumn fields.
 */
final readonly class AdminUserCoreWriteDataMapper
{
    public function __construct(private ?AdminUserFieldRegistryFactory $registryFactory = null)
    {
    }

    /** @return array<string, mixed> column name => value */
    public function map(EntityDataObject $data): array
    {
        return $this->registry()->coreColumnData($data);
    }

    public function registry(): FieldDefinitionRegistry
    {
        return ($this->registryFactory ?? new AdminUserFieldRegistryFactory())->create();
    }
}
