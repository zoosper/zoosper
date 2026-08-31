<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

use InvalidArgumentException;

/** Immutable declarative foreign-key definition. */
final readonly class SchemaForeignKey
{
    public const ACTION_RESTRICT = 'RESTRICT';
    public const ACTION_CASCADE = 'CASCADE';
    public const ACTION_SET_NULL = 'SET NULL';
    public const ACTION_NO_ACTION = 'NO ACTION';

    /**
     * @param non-empty-list<string> $columns
     * @param non-empty-list<string> $referencedColumns
     */
    public function __construct(
        public string $name,
        public array $columns,
        public string $referencedTable,
        public array $referencedColumns,
        public string $onDelete = self::ACTION_RESTRICT,
        public string $onUpdate = self::ACTION_RESTRICT,
    ) {
        if (trim($name) === '' || trim($referencedTable) === '') {
            throw new InvalidArgumentException('Foreign-key name and referenced table are required.');
        }
        if ($columns === [] || count($columns) !== count($referencedColumns)) {
            throw new InvalidArgumentException('Foreign-key local and referenced columns must be non-empty and have equal cardinality.');
        }
        foreach ([$onDelete, $onUpdate] as $action) {
            if (!in_array($action, self::actions(), true)) {
                throw new InvalidArgumentException('Unsupported foreign-key action: ' . $action);
            }
        }
    }

    /** @return list<string> */
    public static function actions(): array
    {
        return [self::ACTION_RESTRICT, self::ACTION_CASCADE, self::ACTION_SET_NULL, self::ACTION_NO_ACTION];
    }
}











