<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

$config = require $basePath . '/config/html_sanitizer.php';
$config['cache_path'] = $basePath . '/' . ltrim((string) ($config['cache_path'] ?? 'var/cache/htmlpurifier'), '/');

$driver = class_exists(\HTMLPurifier::class) ? (string) ($config['driver'] ?? 'htmlpurifier') : 'basic';
$config['driver'] = $driver;

$sanitizer = (new \Zoosper\Core\Html\HtmlSanitizerFactory($config))->create();
$dirty = '<h2>Demo</h2><p onclick="alert(1)">Safe text</p><script>alert("xss")</script><a href="javascript:alert(1)">bad link</a>';
$clean = $sanitizer->sanitise($dirty)->toString();

print "Zoosper HTML sanitizer demo\n";
print "===========================\n\n";
print 'driver: ' . $driver . PHP_EOL . PHP_EOL;
print "Input:\n" . $dirty . PHP_EOL . PHP_EOL;
print "Output:\n" . $clean . PHP_EOL;
