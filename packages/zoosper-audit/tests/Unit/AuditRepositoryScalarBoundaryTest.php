<?php

declare(strict_types=1);

use Zoosper\Audit\Admin\AuditLogGrid;
use Zoosper\Audit\Admin\LoginHistoryGrid;
use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\LoginHistoryRepository;
use Zoosper\Grid\GridCriteria;

it('ignores nested Audit and Login History filters at the repository boundary', function (): void {
    $auditPdo = makeAuditLogPdo();
    seedAuditRows($auditPdo, 2);
    $audit = (new AuditLogRepository($auditPdo))->paginate(GridCriteria::fromValues(
        ['q' => ['nested'], 'entity_type' => ['page']],
        AuditLogGrid::definition(),
    ));

    $loginPdo = makeLoginHistoryPdo();
    seedLoginRows($loginPdo, 2);
    $login = (new LoginHistoryRepository($loginPdo))->paginate(GridCriteria::fromValues(
        ['q' => ['nested'], 'status' => ['success']],
        LoginHistoryGrid::definition(),
    ));

    expect($audit->total)->toBe(2)
        ->and($login->total)->toBe(2);
});