<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Describes how a submitted/admin form field should be persisted.
 *
 * CoreColumn fields are mapped to the entity's primary table. ExtensionTable
 * fields are intentionally kept away from the core SQL write map so third-party
 * module fields cannot accidentally break core insert/update statements.
 */
enum FieldStorageType: string
{
    case CoreColumn = 'core_column';
    case ExtensionTable = 'extension_table';
    case Handler = 'handler';
    case Virtual = 'virtual';
}
