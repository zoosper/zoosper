<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$config = is_file($basePath . '/config/editor.php') ? require $basePath . '/config/editor.php' : [];

print "Zoosper admin content editor diagnostics\n";
print "=======================================\n\n";
print 'default_editor        : ' . ($config['default_editor'] ?? 'missing') . PHP_EOL;
print 'fallback_editor       : ' . ($config['fallback_editor'] ?? 'missing') . PHP_EOL;
print 'current_content_format: ' . ($config['current_content_format'] ?? 'missing') . PHP_EOL;
print 'future_content_format : ' . ($config['future_content_format'] ?? 'missing') . PHP_EOL;
print 'editorjs_enabled      : ' . (!empty($config['editorjs']['enabled']) ? 'yes' : 'no') . PHP_EOL;
print 'editorjs_bundled      : no - adapter hooks only in this phase' . PHP_EOL;
