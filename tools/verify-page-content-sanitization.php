<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper page content sanitisation verification\n";
print "==============================================\n\n";

$checks = [];
$checks['HtmlSanitizerInterface'] = interface_exists(\Zoosper\Core\Html\HtmlSanitizerInterface::class);
$checks['HtmlSanitizerFactory'] = class_exists(\Zoosper\Core\Html\HtmlSanitizerFactory::class);
$checks['PageAdminController'] = class_exists(\Zoosper\Admin\Controller\PageAdminController::class);

$config = require $basePath . '/config/html_sanitizer.php';
$config['cache_path'] = $basePath . '/' . ltrim((string) ($config['cache_path'] ?? 'var/cache/htmlpurifier'), '/');
$sanitizer = (new \Zoosper\Core\Html\HtmlSanitizerFactory($config))->create();
$dirty = '<p onclick="alert(1)">Hello</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a>';
$clean = $sanitizer->sanitise($dirty)->toString();
$checks['sanitizer_strips_script'] = !str_contains(strtolower($clean), '<script');
$checks['sanitizer_strips_event_handlers'] = !str_contains(strtolower($clean), 'onclick');
$checks['sanitizer_strips_javascript_urls'] = !str_contains(strtolower($clean), 'javascript:');

$reflection = new ReflectionClass(\Zoosper\Admin\Controller\PageAdminController::class);
$constructor = $reflection->getConstructor();
$constructorTypes = [];
foreach ($constructor?->getParameters() ?? [] as $parameter) {
    $type = $parameter->getType();
    $constructorTypes[] = $type instanceof ReflectionNamedType ? $type->getName() : '';
}
$checks['controller_accepts_sanitizer'] = in_array(\Zoosper\Core\Html\HtmlSanitizerInterface::class, $constructorTypes, true);

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
