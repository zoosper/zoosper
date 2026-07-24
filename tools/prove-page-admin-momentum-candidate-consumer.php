<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageMomentumAggregatorCandidateConsumer;

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
$candidatePath = $root . '/app/zoosper-page/config/admin_page_momentum_runtime_candidate.php';

$report[] = '## Page Admin Momentum Candidate Consumer Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!class_exists(PageMomentumAggregatorCandidateConsumer::class)) {
    $report[] = 'Candidate consumer autoloadable: no';
    $errors++;
} elseif (!is_file($candidatePath)) {
    $report[] = 'Candidate config missing. Run tools/apply-page-admin-momentum-aggregator-candidate.php first.';
    $errors++;
} else {
    $candidate = require $candidatePath;
    $consumed = (new PageMomentumAggregatorCandidateConsumer())->consume(is_array($candidate) ? $candidate : []);

    $valid = $consumed['enabled'] === true
        && $consumed['routeCount'] === 1
        && $consumed['menuCount'] === 1
        && ($consumed['routes'][0]['name'] ?? '') === 'admin.page_momentum.index'
        && ($consumed['menuItems'][0]['route'] ?? '') === 'admin.page_momentum.index'
        && $consumed['liveMutation'] === false
        && count($consumed['rollback']) > 0;

    $report[] = 'Candidate consumer autoloadable: yes';
    $report[] = 'Candidate enabled: ' . ($consumed['enabled'] ? 'yes' : 'no');
    $report[] = 'Consumed route count: ' . $consumed['routeCount'];
    $report[] = 'Consumed menu count: ' . $consumed['menuCount'];
    $report[] = 'Live mutation performed: ' . ($consumed['liveMutation'] ? 'yes' : 'no');
    $report[] = 'Rollback steps: ' . count($consumed['rollback']);
    $report[] = 'Candidate consumer valid: ' . ($valid ? 'yes' : 'no');

    if (!$valid) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Existing aggregator files overwritten: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/page-admin-momentum-candidate-consumer-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-admin-momentum-candidate-consumer-proof.log', "PAGE_ADMIN_MOMENTUM_CANDIDATE_CONSUMER_WARNINGS {$warnings}\nPAGE_ADMIN_MOMENTUM_CANDIDATE_CONSUMER_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
