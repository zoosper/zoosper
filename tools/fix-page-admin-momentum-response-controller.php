<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminHttpController;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}
require $autoload;

$errors = 0;
$warnings = 0;
$report = [];
$files = array_values(array_filter([
    $root . '/app/zoosper-page/config/admin_routes.php',
    $root . '/app/zoosper-page/config/routes.php',
    $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
    $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php',
    $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php',
], 'is_file'));
$backupDir = $root . '/var/backups/page-admin-momentum-response-controller';

$report[] = '## Page Momentum Response Controller Fix';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminHttpController::class)) {
    $report[] = 'HTTP controller autoloadable: no';
    $errors++;
} else {
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0775, true);
    }

    foreach ($files as $file) {
        $config = require $file;
        if (!is_array($config)) {
            $warnings++;
            $report[] = '- skipped non-array config: ' . str_replace($root . '/', '', $file);
            continue;
        }

        $updated = replaceController($config);
        if ($updated !== $config) {
            copy($file, $backupDir . '/' . basename($file) . '.bak');
            file_put_contents($file, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($updated, true) . ";\n");
            $report[] = '- updated controller in: ' . str_replace($root . '/', '', $file);
        } else {
            $report[] = '- already ok or no route controller found: ' . str_replace($root . '/', '', $file);
        }
    }
}

$report[] = 'Backups directory: var/backups/page-admin-momentum-response-controller';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-response-controller-fix.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-response-controller-fix.log', "PAGE_ADMIN_MOMENTUM_RESPONSE_CONTROLLER_FIX_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_RESPONSE_CONTROLLER_FIX_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);

/**
 * @param mixed $value
 * @return mixed
 */
function replaceController(mixed $value): mixed
{
    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        if ($key === 'controller' && is_string($item) && str_ends_with($item, 'PageMomentumAdminController')) {
            $value[$key] = PageMomentumAdminHttpController::class;
            continue;
        }
        $value[$key] = replaceController($item);
    }

    return $value;
}
