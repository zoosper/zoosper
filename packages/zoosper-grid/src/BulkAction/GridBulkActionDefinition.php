<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Immutable, module-registerable description of one Grid bulk action. */
final readonly class GridBulkActionDefinition
{
    public function __construct(
        public string $id,
        public string $label,
        public GridBulkSelectionScope $selectionScope,
        public GridBulkExecutionType $executionType,
        public GridBulkConfirmationPolicy $confirmationPolicy = GridBulkConfirmationPolicy::NONE,
        public ?string $requiredPermission = null,
        public int $maximumSelection = 100,
        public bool $auditRequired = false,
    ) {
        if (preg_match('/^[a-z][a-z0-9._-]{1,63}$/', $id) !== 1) {
            throw new InvalidArgumentException(
                'Grid bulk action ID must be 2-64 lowercase characters using letters, numbers, dot, underscore or hyphen.',
            );
        }
        if (trim($label) === '') {
            throw new InvalidArgumentException('Grid bulk action label cannot be empty.');
        }
        if ($maximumSelection < 1 || $maximumSelection > 10_000) {
            throw new InvalidArgumentException('Grid bulk action maximum selection must be between 1 and 10000.');
        }
        if ($executionType === GridBulkExecutionType::CLIENT_DOWNLOAD && $auditRequired) {
            throw new InvalidArgumentException('Client-only Grid downloads cannot claim server audit coverage.');
        }
        if ($executionType === GridBulkExecutionType::SERVER_MUTATION
            && $confirmationPolicy === GridBulkConfirmationPolicy::NONE) {
            throw new InvalidArgumentException('Server Grid mutations require an explicit confirmation policy.');
        }
        if ($executionType === GridBulkExecutionType::REMOTE_MUTATION
            && $confirmationPolicy === GridBulkConfirmationPolicy::NONE) {
            throw new InvalidArgumentException('Remote Grid mutations require an explicit confirmation policy.');
        }
    }
}











