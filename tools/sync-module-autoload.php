<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

$synchronizer = new \Zoosper\Core\Composer\ModuleAutoloadSynchronizer($basePath);
$result = $synchronizer->sync();

echo "Zoosper module autoload synchronisation\n";
echo "======================================\n\n";
echo 'Application mappings : ' . count($result['autoload']) . PHP_EOL;
echo 'Test mappings        : ' . count($result['autoload-dev']) . PHP_EOL;
echo 'composer.json changed: ' . ($result['changed'] ? 'yes' : 'no') . PHP_EOL;
echo "\nRun: PHP=php8.5 composer dump-autoload\n";
