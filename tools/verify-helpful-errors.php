<?php

declare(strict_types=1);

/**
 * Verify developer-friendly exception classes and formatting.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\Core\\Exception\\ZoosperException',
    'Zoosper\\Core\\Exception\\SensitiveValueRedactor',
    'Zoosper\\Core\\Exception\\ConsoleExceptionFormatter',
];

print "Zoosper helpful error verification\n";
print "==================================\n\n";
$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

try {
    (new \Zoosper\Core\Container\ServiceContainer())->get('missing.service');
    print '- missing service helpful exception: FAIL' . PHP_EOL;
    $failed = true;
} catch (\Zoosper\Core\Exception\ZoosperException $exception) {
    $hasSuggestion = $exception->suggestion() !== '';
    $hasContext = $exception->context() !== '';
    print '- missing service helpful exception: ' . ($hasSuggestion && $hasContext ? 'ok' : 'incomplete') . PHP_EOL;
    $failed = $failed || !$hasSuggestion || !$hasContext;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
