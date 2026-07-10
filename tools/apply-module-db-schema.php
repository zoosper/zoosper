<?php

declare(strict_types=1);

/**
 * Apply every enabled module's config/db_schema.php file.
 *
 * This command is an immediate safety tool while the main `bin/zoosper migrate`
 * command is being updated to call DeclarativeSchemaApplier directly.
 */

$basePath = dirname(__DIR__);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false && $value !== '' ? $value : $default;
    }
}

require $basePath . '/vendor/autoload.php';

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Database\DeclarativeSchemaApplier;
use Zoosper\Core\Module\ModuleRegistry;

$config = ConfigRepository::fromPath($basePath . '/config');
$pdo = (new ConnectionFactory($config, $basePath))->create();
$modules = new ModuleRegistry($basePath);

$messages = (new DeclarativeSchemaApplier($pdo, $modules))->applyAll();

foreach ($messages as $message) {
    echo $message . PHP_EOL;
}

echo 'Module declarative schemas applied.' . PHP_EOL;
