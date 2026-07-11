<?php

declare(strict_types=1);

/**
 * Verify Latte template engine integration.
 */

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper Latte template engine verification\n";
print "==========================================\n\n";

$failed = false;
$checks = [
    'Latte\\Engine',
    'Zoosper\\Theme\\Template\\Engine\\LatteTemplateEngine',
    'Zoosper\\Theme\\Template\\Engine\\TemplateEngineRegistry',
];

foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

try {
    $engine = new \Zoosper\Theme\Template\Engine\LatteTemplateEngine($basePath . '/var/cache/templates');
    $registry = new \Zoosper\Theme\Template\Engine\TemplateEngineRegistry(
        $engine,
        new \Zoosper\Theme\Template\Engine\PhpTemplateEngine(),
    );
    $extensions = $registry->extensions();
    print '- registered_extensions: ' . (in_array('latte', $extensions, true) && in_array('php', $extensions, true) ? 'ok' : 'check') . ' (' . implode(', ', $extensions) . ')' . PHP_EOL;

    $output = $engine->renderFile($basePath . '/themes/default/templates/examples/hello.latte', [
        'title' => 'Latte is working',
        'message' => 'Zoosper can render .latte templates through the adapter.',
        'staticCss' => '/static/themes/default/assets/css/app.css',
    ]);
    $rendered = str_contains($output, 'Latte is working') && str_contains($output, 'Zoosper can render');
    print '- sample_render: ' . ($rendered ? 'ok' : 'check') . PHP_EOL;
    $failed = $failed || !$rendered;
} catch (Throwable $exception) {
    print '- sample_render: FAIL - ' . $exception->getMessage() . PHP_EOL;
    $failed = true;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
