<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageMomentumAdminController;
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
$backupDir = $root . '/var/backups/page-admin-momentum-metadata-controller';

$metadataFiles = array_values(array_filter([
    $root . '/app/zoosper-page/config/admin_page_momentum_routes.php',
    $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php',
    $root . '/app/zoosper-page/config/admin_page_momentum_hook_candidate.php',
], 'is_file'));

$liveRouteFiles = array_values(array_filter([
    $root . '/app/zoosper-page/config/admin_routes.php',
    $root . '/app/zoosper-page/config/routes.php',
], 'is_file'));

$report[] = '## Page Momentum Metadata Controller Repair';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAdminController::class) || !class_exists(PageMomentumAdminHttpController::class)) {
    $report[] = 'Required Page Momentum controller classes are not autoloadable.';
    $errors++;
} else {
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0775, true);
    }

    foreach ($metadataFiles as $file) {
        $config = require $file;
        if (!is_array($config)) {
            $warnings++;
            $report[] = '- skipped non-array metadata config: ' . str_replace($root . '/', '', $file);
            continue;
        }

        $updated = replaceController($config, PageMomentumAdminHttpController::class, PageMomentumAdminController::class);
        if ($updated !== $config) {
            copy($file, $backupDir . '/' . basename($file) . '.bak');
            file_put_contents($file, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($updated, true) . ";\n");
            $report[] = '- restored metadata renderer controller in: ' . str_replace($root . '/', '', $file);
        } else {
            $report[] = '- metadata already canonical or no controller found: ' . str_replace($root . '/', '', $file);
        }
    }

    foreach ($liveRouteFiles as $file) {
        $config = require $file;
        if (!is_array($config)) {
            $warnings++;
            $report[] = '- skipped non-array live route config: ' . str_replace($root . '/', '', $file);
            continue;
        }

        $updated = replaceController($config, PageMomentumAdminController::class, PageMomentumAdminHttpController::class);
        if ($updated !== $config) {
            copy($file, $backupDir . '/' . basename($file) . '.live.bak');
            file_put_contents($file, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($updated, true) . ";\n");
            $report[] = '- ensured live route uses HTTP controller in: ' . str_replace($root . '/', '', $file);
        } else {
            $report[] = '- live route already HTTP-safe or no controller found: ' . str_replace($root . '/', '', $file);
        }
    }
}

$report[] = '';
$report[] = 'Canonical metadata controller: ' . PageMomentumAdminController::class;
$report[] = 'Live route controller: ' . PageMomentumAdminHttpController::class;
$report[] = 'Backups directory: var/backups/page-admin-momentum-metadata-controller';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-metadata-controller-repair.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-metadata-controller-repair.log', "PAGE_ADMIN_MOMENTUM_METADATA_CONTROLLER_REPAIR_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_METADATA_CONTROLLER_REPAIR_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);

/**
 * @param mixed $value
 * @return mixed
 */
function replaceController(mixed $value, string $from, string $to): mixed
{
    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        if ($key === 'controller' && $item === $from) {
            $value[$key] = $to;
            continue;
        }
        $value[$key] = replaceController($item, $from, $to);
    }

    return $value;
}
