<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$bundle = $basePath . '/public/assets/admin/js/editorjs.bundle.js';
$adapter = $basePath . '/public/assets/admin/js/zoosper-content-editor.js';
$css = $basePath . '/public/assets/admin/css/zoosper-content-editor.css';

print "Zoosper Editor.js runtime diagnostics\n";
print "====================================\n\n";
print 'editorjs_bundle_exists : ' . (is_file($bundle) ? 'yes' : 'no') . PHP_EOL;
print 'adapter_script_exists  : ' . (is_file($adapter) ? 'yes' : 'no') . PHP_EOL;
print 'editor_css_exists      : ' . (is_file($css) ? 'yes' : 'no') . PHP_EOL;
print 'storage_mode           : html bridge via textarea[name=content]' . PHP_EOL;
print 'block_json_storage     : not active in this phase' . PHP_EOL;
