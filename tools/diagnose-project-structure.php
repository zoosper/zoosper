<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$roots = array_filter(scandir($basePath) ?: [], static fn (string $item): bool => !in_array($item, ['.', '..'], true));

print "Zoosper project structure diagnostics\n";
print "====================================\n\n";
foreach ($roots as $root) {
    $path = $basePath . '/' . $root;
    $type = is_dir($path) ? 'dir ' : 'file';
    print sprintf('%-4s %s%s', $type, $root, PHP_EOL);
}
