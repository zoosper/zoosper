<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\EntityDataObject;

/**
 * Builds SQL-safe AdminUser update fragments from the save pipeline write map.
 *
 * This class deliberately does not know about raw POST or arbitrary data-object
 * keys. It receives an EntityDataObject and uses AdminUserCoreWriteDataMapper to
 * obtain only field-definition approved core columns before generating SQL.
 */
final readonly class AdminUserCoreWriteSqlBuilder
{
    public function __construct(private ?AdminUserCoreWriteDataMapper $mapper = null)
    {
    }

    /**
     * Builds an UPDATE statement and parameter array for admin_users.
     *
     * @return array{sql: string, params: array<string, mixed>}
     */
    public function buildUpdate(int $id, EntityDataObject $data): array
    {
        $coreData = ($this->mapper ?? new AdminUserCoreWriteDataMapper())->map($data);
        unset($coreData['id']);

        if ($coreData === []) {
            return [
                'sql' => '',
                'params' => ['id' => $id],
            ];
        }

        $setFragments = [];
        $params = ['id' => $id];
        foreach ($coreData as $column => $value) {
            $parameter = 'field_' . $column;
            $setFragments[] = $column . ' = :' . $parameter;
            $params[$parameter] = $value;
        }

        return [
            'sql' => 'UPDATE admin_users SET ' . implode(', ', $setFragments) . ' WHERE id = :id',
            'params' => $params,
        ];
    }
}
