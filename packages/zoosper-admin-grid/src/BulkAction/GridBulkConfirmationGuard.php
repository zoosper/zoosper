<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;

/** Validates explicit confirmation without trusting the action label. */
final readonly class GridBulkConfirmationGuard
{
    /** @param array<string, mixed> $form */
    public function assertConfirmed(GridBulkActionDefinition $definition, array $form): void
    {
        if ($definition->confirmationPolicy === GridBulkConfirmationPolicy::NONE) {
            return;
        }

        $confirmedAction = trim((string) ($form['confirmed_action'] ?? ''));
        if ($confirmedAction === '' || !hash_equals($definition->id, $confirmedAction)) {
            throw new InvalidArgumentException(
                sprintf('Grid bulk action "%s" requires explicit confirmation.', $definition->id),
            );
        }
    }
}
