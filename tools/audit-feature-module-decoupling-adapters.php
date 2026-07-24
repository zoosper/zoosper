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

$report[] = '## Feature Module Decoupling Adapter Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$checks = [
    'Zoosper\\Core\\Routing\\FallbackHandlerInterface' => 'interface',
    'Zoosper\\Page\\Routing\\PageFallbackHandlerAdapter' => 'class',
    'Zoosper\\Core\\Site\\SiteContextProviderInterface' => 'interface',
    'Zoosper\\Site\\Site\\SiteContextProviderAdapter' => 'class',
];

foreach ($checks as $symbol => $kind) {
    $exists = $kind === 'interface' ? interface_exists($symbol) : class_exists($symbol);
    $report[] = '- ' . $symbol . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$pageAdapterOk = false;
$siteAdapterOk = false;

if (class_exists('Zoosper\\Page\\Routing\\PageFallbackHandlerAdapter') && interface_exists('Zoosper\\Core\\Routing\\FallbackHandlerInterface')) {
    $adapter = new \Zoosper\Page\Routing\PageFallbackHandlerAdapter();
    $pageAdapterOk = $adapter instanceof \Zoosper\Core\Routing\FallbackHandlerInterface
        && $adapter->supports(new stdClass()) === false
        && $adapter->handle(new stdClass()) === null;
}

if (class_exists('Zoosper\\Site\\Site\\SiteContextProviderAdapter') && interface_exists('Zoosper\\Core\\Site\\SiteContextProviderInterface')) {
    $adapter = new \Zoosper\Site\Site\SiteContextProviderAdapter();
    $siteAdapterOk = $adapter instanceof \Zoosper\Core\Site\SiteContextProviderInterface
        && $adapter->resolve(new stdClass()) === null;
}

$report[] = '';
$report[] = 'Page fallback adapter safe no-op: ' . ($pageAdapterOk ? 'yes' : 'no');
$report[] = 'Site context provider adapter safe no-op: ' . ($siteAdapterOk ? 'yes' : 'no');
$report[] = 'Runtime fallback rewired: no';
$report[] = 'Runtime site context binding changed: no';

if (!$pageAdapterOk || !$siteAdapterOk) {
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/feature-module-decoupling-adapters-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/feature-module-decoupling-adapters-audit.log', "FEATURE_MODULE_DECOUPLING_ADAPTERS_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
