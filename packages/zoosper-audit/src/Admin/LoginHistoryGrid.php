<?php

declare(strict_types=1);

namespace Zoosper\Audit\Admin;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

/**
 * Declarative Grid definition for the Login History admin screen.
 *
 * Columns match the original bespoke template (Time/Email/Status/IP). The
 * status filter's options list the exact status values LoginController and
 * AdminTwoFactorChallengeController write as of Phase 1.113: 'success',
 * 'failed' (password stage), 'password_ok_pending_2fa', and 'otp_failed'
 * (2FA challenge stage) — this is what makes "check individual login actions
 * to see if someone is doing bad activity" practical: repeated otp_failed or
 * failed rows for one email/IP are now filterable and sortable.
 */
final class LoginHistoryGrid
{
    public static function definition(): GridDefinition
    {
        return new GridDefinition(
            title: 'Login History',
            columns: [
                new GridColumn('created_at', 'Time', sortable: true),
                new GridColumn('email', 'Email'),
                new GridColumn(
                    'status',
                    'Status',
                    render: static fn (mixed $value): string => '<code>' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</code>',
                ),
                new GridColumn('ip_address', 'IP'),
            ],
            filters: [
                new GridFilter('q', 'Search email'),
                new GridFilter('status', 'Status', type: 'select', options: [
                    ['value' => 'success', 'label' => 'Success'],
                    ['value' => 'failed', 'label' => 'Failed (password)'],
                    ['value' => 'password_ok_pending_2fa', 'label' => 'Password OK, pending 2FA'],
                    ['value' => 'otp_failed', 'label' => 'Failed (2FA code)'],
                ]),
            ],
            defaultSort: 'created_at',
            defaultSortDir: 'desc',
            emptyMessage: 'No login history yet.',
        );
    }
}










