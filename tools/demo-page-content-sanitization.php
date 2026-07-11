<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$config = require $basePath . '/config/html_sanitizer.php';
$config['cache_path'] = $basePath . '/' . ltrim((string) ($config['cache_path'] ?? 'var/cache/htmlpurifier'), '/');

$sanitizer = (new \Zoosper\Core\Html\HtmlSanitizerFactory($config))->create();
$input = '<h2>CMS Body</h2><p onclick="alert(1)">Editor text</p><script>alert(1)</script><a href="javascript:alert(1)">unsafe link</a>';
$output = $sanitizer->sanitise($input)->toString();

print "Zoosper page content sanitisation demo\n";
print "======================================\n\n";
print "Input:\n" . $input . "\n\n";
print "Sanitised output:\n" . $output . "\n";
