<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = 0;
$report = [];

$report[] = '## Page Admin Momentum Wiring Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = '### Proposed route metadata';
$report[] = '- route: admin.page_momentum.index';
$report[] = '- method: GET';
$report[] = '- path: /admin/page-momentum';
$report[] = '- permission: page.manage';
$report[] = '- view: admin/page-momentum.latte';
$report[] = '';
$report[] = '### Proposed menu metadata';
$report[] = '- label: Page momentum';
$report[] = '- route: admin.page_momentum.index';
$report[] = '- permission: page.manage';
$report[] = '- sort_order: 95';
$report[] = '';
$report[] = '### Safe cutover checklist for future phase';
$report[] = '1. Confirm page module route config shape and router registration conventions.';
$report[] = '2. Confirm admin menu config shape and permission conventions.';
$report[] = '3. Add route behind admin middleware and page.manage permission.';
$report[] = '4. Add menu item only after route smoke passes.';
$report[] = '5. Keep panel read-only.';
$report[] = '6. Add nginx/exception log smoke check after deployment.';
$report[] = '';
$report[] = 'Runtime route changed: no';
$report[] = 'Admin menu changed: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-wiring-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-wiring-plan.log', "PAGE_ADMIN_MOMENTUM_WIRING_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
