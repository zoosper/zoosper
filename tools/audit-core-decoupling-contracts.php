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
$requiredClasses = [
    'Zoosper\\Core\\Routing\\FallbackHandlerInterface',
    'Zoosper\\Core\\Routing\\NullFallbackHandler',
    'Zoosper\\Core\\Site\\SiteContextProviderInterface',
    'Zoosper\\Core\\Site\\NullSiteContextProvider',
];

$report[] = '## Core Decoupling Contracts Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredClasses as $class) {
    $exists = class_exists($class) || interface_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$nullFallbackOk = false;
$nullSiteProviderOk = false;

if (class_exists('Zoosper\\Core\\Routing\\NullFallbackHandler')) {
    $handler = new \Zoosper\Core\Routing\NullFallbackHandler();
    $nullFallbackOk = !$handler->supports(new stdClass()) && $handler->handle(new stdClass()) === null;
}

if (class_exists('Zoosper\\Core\\Site\\NullSiteContextProvider')) {
    $provider = new \Zoosper\Core\Site\NullSiteContextProvider();
    $nullSiteProviderOk = $provider->resolve(new stdClass()) === null;
}

$report[] = '';
$report[] = 'Null fallback safe default: ' . ($nullFallbackOk ? 'yes' : 'no');
$report[] = 'Null site context provider safe default: ' . ($nullSiteProviderOk ? 'yes' : 'no');
$report[] = 'Runtime fallback rewired: no';
$report[] = 'Runtime site context binding changed: no';

if (!$nullFallbackOk || !$nullSiteProviderOk) {
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/core-decoupling-contracts-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-decoupling-contracts-audit.log', "CORE_DECOUPLING_CONTRACTS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
