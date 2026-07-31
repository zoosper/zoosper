<?php

declare(strict_types=1);

use Zoosper\Grid\GridColumn;

/**
 * Phase B2 PROOF-OF-CONCEPT: demonstrates the module-extends-another-module's-
 * grid mechanism using GridColumnRegistry.
 *
 * The login-history grid (owned by zoosper-admin) does not display
 * `user_agent`, even though LoginHistoryRepository::record() has always
 * captured it. This file is deliberately chosen to be REAL, already-existing
 * data (no fabricated demo values) so the wiring can be verified honestly:
 * once discovered, "User Agent" appears as an extra column with zero changes
 * to LoginHistoryGrid.php or LoginHistoryController.php.
 *
 * Any module can contribute to any grid key this way — the grid owner does
 * not need to know in advance which modules will extend it.
 */
return [
    'login-history' => [
        'columns' => [
            new GridColumn(
                'user_agent',
                'User Agent',
                render: static function (mixed $value): string {
                    $value = (string) ($value ?? '');
                    if ($value === '') {
                        return '<span class="muted">&mdash;</span>';
                    }

                    // Long user-agent strings are truncated for grid readability;
                    // the full value is still present in the raw row/database.
                    $display = mb_strlen($value) > 60 ? mb_substr($value, 0, 57) . '...' : $value;

                    return htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
                },
            ),
        ],
        'filters' => [],
    ],
];

