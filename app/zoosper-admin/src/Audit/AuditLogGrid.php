<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use Zoosper\Core\Grid\GridColumn;
use Zoosper\Core\Grid\GridDefinition;
use Zoosper\Core\Grid\GridFilter;

/**
 * Declarative Grid definition for the Audit Log admin screen.
 *
 * Columns match the original bespoke template exactly (Time/Actor/Action/
 * Entity/ID/Summary); the only behavioural addition is that Time is now
 * sortable and the grid supports free-text + entity_type filtering.
 */
final class AuditLogGrid
{
    public static function definition(): GridDefinition
    {
        return new GridDefinition(
            title: 'Audit Log',
            columns: [
                new GridColumn('created_at', 'Time', sortable: true),
                new GridColumn('actor_email', 'Actor'),
                new GridColumn(
                    'action',
                    'Action',
                    render: static fn (mixed $value): string => '<code>' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</code>',
                ),
                new GridColumn('entity_type', 'Entity'),
                new GridColumn('entity_id', 'ID'),
                new GridColumn('summary', 'Summary'),
            ],
            filters: [
                new GridFilter('q', 'Search action/actor/summary'),
                // Left as free text rather than a select: entity_type values are
                // module-defined (page, admin_role, site, admin_user, ...) and
                // new ones will appear as new modules record activity, so a
                // fixed option list would silently go stale.
                new GridFilter('entity_type', 'Entity type'),
            ],
            defaultSort: 'created_at',
            defaultSortDir: 'desc',
            emptyMessage: 'No audit records yet.',
        );
    }
}
