<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper HTML sanitizer verification\n";
print "===================================\n\n";

$classes = [
    \Zoosper\Core\Html\SanitizedHtml::class,
    \Zoosper\Core\Html\HtmlSanitizerInterface::class,
    \Zoosper\Core\Html\BasicHtmlSanitizer::class,
    \Zoosper\Core\Html\HtmlPurifierSanitizer::class,
    \Zoosper\Core\Html\HtmlSanitizerFactory::class,
];

$failed = false;
foreach ($classes as $class) {
    $ok = class_exists($class) || interface_exists($class);
    print '- ' . $class . ': ' . ($ok ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$ok;
}

$configFile = $basePath . '/config/html_sanitizer.php';
$config = is_file($configFile) ? require $configFile : [];
print '- config/html_sanitizer.php: ' . (is_array($config) ? 'ok' : 'invalid') . PHP_EOL;
$failed = $failed || !is_array($config);

$basic = new \Zoosper\Core\Html\BasicHtmlSanitizer();
$dirty = '<p>Hello</p><script>alert(1)</script><a href="javascript:alert(1)" onclick="alert(2)">bad</a>';
$clean = $basic->sanitise($dirty)->toString();
$basicOk = !str_contains(strtolower($clean), '<script') && !str_contains(strtolower($clean), 'onclick') && !str_contains(strtolower($clean), 'javascript:');
print '- basic sanitizer smoke test: ' . ($basicOk ? 'ok' : 'check') . PHP_EOL;
$failed = $failed || !$basicOk;

$purifierInstalled = class_exists(\HTMLPurifier::class);
print '- HTMLPurifier dependency: ' . ($purifierInstalled ? 'installed' : 'not installed') . PHP_EOL;

if ($purifierInstalled) {
    $purifierOptions = $config;
    $purifierOptions['cache_path'] = $basePath . '/' . ltrim((string) ($purifierOptions['cache_path'] ?? 'var/cache/htmlpurifier'), '/');
    $purifier = new \Zoosper\Core\Html\HtmlPurifierSanitizer($purifierOptions);
    $purified = $purifier->sanitise($dirty)->toString();
    $purifierOk = !str_contains(strtolower($purified), '<script') && !str_contains(strtolower($purified), 'onclick') && !str_contains(strtolower($purified), 'javascript:');
    print '- HTMLPurifier sanitizer smoke test: ' . ($purifierOk ? 'ok' : 'check') . PHP_EOL;
    $failed = $failed || !$purifierOk;
} else {
    print '- HTMLPurifier sanitizer smoke test: skipped until composer dependency is installed' . PHP_EOL;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
