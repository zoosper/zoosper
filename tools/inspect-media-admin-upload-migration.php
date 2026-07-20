<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = $basePath . '/packages/zoosper-media/src/Controller/MediaAdminController.php';
$service = $basePath . '/packages/zoosper-media/src/Service/MediaUploadService.php';
$out = $basePath . '/media-admin-upload-migration-inspection.txt';

$lines = [];
$lines[] = 'ZOOSPER CMS - MEDIA ADMIN UPLOAD MIGRATION INSPECTION';
$lines[] = str_repeat('=', 84);
$lines[] = 'Generated : ' . gmdate('c');
$lines[] = 'Repo root : ' . $basePath;
$lines[] = 'PCI note  : source only; no .env, uploaded media, secrets or table data read.';
$lines[] = str_repeat('=', 84);
$lines[] = '';

$source = is_file($controller) ? (string) file_get_contents($controller) : '';
$serviceSource = is_file($service) ? (string) file_get_contents($service) : '';

$signals = [
    'MediaAdminController exists' => is_file($controller),
    'MediaUploadService exists' => is_file($service),
    'Admin controller mentions MediaUploadService' => str_contains($source, 'MediaUploadService'),
    'Admin controller directly calls storage->store' => str_contains($source, '->storage->store'),
    'Admin controller directly calls assets->create' => str_contains($source, '->assets->create'),
    'Admin controller has normaliseOriginalFilename helper' => str_contains($source, 'normaliseOriginalFilename'),
    'Admin controller has currentAdminUser helper' => str_contains($source, 'currentAdminUser'),
    'Shared service has cleanup delegation' => str_contains($serviceSource, '->cleanup->cleanup($stored)'),
];

$lines[] = 'SIGNALS';
$lines[] = str_repeat('-', 84);
foreach ($signals as $label => $value) {
    $lines[] = '- ' . $label . ': ' . ($value ? 'yes' : 'no');
}
$lines[] = '';

if (preg_match('/public function __construct\s*\((.*?)\)\s*\{/s', $source, $match)) {
    $lines[] = 'CONSTRUCTOR SIGNATURE';
    $lines[] = str_repeat('-', 84);
    $lines[] = trim($match[1]);
    $lines[] = '';
}

if (preg_match('/public function upload\s*\((.*?)\)\s*:\s*([^\s{]+)\s*\{(.*?)(?:\n    public function|\n    private function|\n}\s*$)/s', $source, $match)) {
    $lines[] = 'UPLOAD METHOD';
    $lines[] = str_repeat('-', 84);
    $lines[] = 'Parameters: ' . trim($match[1]);
    $lines[] = 'Return type: ' . trim($match[2]);
    $lines[] = trim($match[3]);
    $lines[] = '';
}

$lines[] = 'FULL FILE: packages/zoosper-media/src/Controller/MediaAdminController.php';
$lines[] = str_repeat('-', 84);
$lines[] = $source !== '' ? $source : '(missing)';
$lines[] = '';

file_put_contents($out, implode(PHP_EOL, $lines) . PHP_EOL);

print 'Media admin upload migration inspection written to: ' . basename($out) . PHP_EOL;
foreach ($signals as $label => $value) {
    print '- ' . $label . ': ' . ($value ? 'yes' : 'no') . PHP_EOL;
}
