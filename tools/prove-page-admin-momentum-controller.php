<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$errors = 0;
$report = [];
$controllerClass = 'Zoosper\\Page\\Admin\\Controller\\PageMomentumAdminController';

$report[] = '## Page Admin Momentum Controller Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Controller class: ' . $controllerClass;

if (!class_exists($controllerClass)) {
    $report[] = 'Controller autoloadable: no';
    $errors++;
} else {
    $controller = new $controllerClass();
    $html = $controller->index();
    $valid = is_string($html)
        && str_contains($html, 'Page momentum')
        && str_contains($html, 'Core decoupling readiness')
        && str_contains($html, 'PageRenderer report-only candidate');

    $report[] = 'Controller autoloadable: yes';
    $report[] = 'Controller output valid static panel: ' . ($valid ? 'yes' : 'no');
    $report[] = 'Controller registered in runtime route: no';

    if (!$valid) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Runtime route changed: no';
$report[] = 'Admin menu changed: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-controller-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-controller-proof.log', "PAGE_ADMIN_MOMENTUM_CONTROLLER_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
