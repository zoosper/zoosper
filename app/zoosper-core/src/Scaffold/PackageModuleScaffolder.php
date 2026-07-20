<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

use RuntimeException;

/**
 * Scaffolds a Composer-package Zoosper module under packages/.
 *
 * This supports the "blank USB" product direction: the core stays thin while
 * capabilities are added as removable Composer-style modules.
 */
final readonly class PackageModuleScaffolder
{
    public function __construct(private string $basePath)
    {
    }

    public function scaffold(string $input): PackageModuleScaffoldResult
    {
        $identity = $this->normaliseIdentity($input);
        $packagePath = rtrim($this->basePath, '/\\') . '/packages/' . $identity['package_dir'];

        if (is_dir($packagePath)) {
            throw new RuntimeException('Package module already exists: ' . $packagePath);
        }

        $files = $this->files($identity);
        $created = [];
        foreach ($files as $relative => $contents) {
            $path = $packagePath . '/' . $relative;
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('Unable to create package module directory: ' . $directory);
            }
            file_put_contents($path, $contents);
            $created[] = 'packages/' . $identity['package_dir'] . '/' . $relative;
        }

        return new PackageModuleScaffoldResult(
            packageName: $identity['package_name'],
            moduleName: $identity['module_name'],
            namespace: $identity['namespace'],
            packagePath: $packagePath,
            createdFiles: $created,
        );
    }

    /** @return array{vendor: string, module: string, package_name: string, package_dir: string, module_name: string, namespace: string, class_prefix: string} */
    private function normaliseIdentity(string $input): array
    {
        $input = trim($input);
        if ($input === '' || !preg_match('~^[A-Za-z][A-Za-z0-9]*(?:[\\/_-][A-Za-z][A-Za-z0-9]*)+$~', $input)) {
            throw new RuntimeException('Package module name must look like Vendor/Module, Vendor_Module or vendor/module.');
        }

        $parts = preg_split('~[\\/_-]+~', $input) ?: [];
        if (count($parts) < 2) {
            throw new RuntimeException('Package module name must include vendor and module parts.');
        }

        $vendor = $this->studly((string) $parts[0]);
        $moduleParts = array_values(array_map('strval', array_slice($parts, 1)));
        $module = implode('', array_map(fn (string $part): string => $this->studly($part), $moduleParts));
        $vendorPackage = $this->kebab((string) $parts[0]);
        $modulePackage = implode('-', array_map(fn (string $part): string => $this->kebab($part), $moduleParts));

        return [
            'vendor' => $vendor,
            'module' => $module,
            'package_name' => $vendorPackage . '/' . $modulePackage,
            'package_dir' => $vendorPackage . '-' . $modulePackage,
            'module_name' => $vendor . '_' . $module,
            'namespace' => $vendor . '\\' . $module . '\\',
            'class_prefix' => $module,
        ];
    }

    private function studly(string $value): string
    {
        $parts = preg_split('/[^A-Za-z0-9]+/', $value) ?: [];
        $normalised = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $normalised[] = ucfirst($part);
        }

        return implode('', $normalised);
    }

    private function kebab(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '-$0', $value) ?: $value;
        $value = preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?: $value;

        return trim(strtolower($value), '-');
    }

    /** @param array<string, string> $identity @return array<string, string> */
    private function files(array $identity): array
    {
        $namespace = $identity['namespace'];
        $namespaceEscaped = str_replace('\\', '\\\\', $namespace);
        $packageName = $identity['package_name'];
        $moduleName = $identity['module_name'];
        $classPrefix = $identity['class_prefix'];

        return [
            'composer.json' => <<<JSON
{
    "name": "{$packageName}",
    "description": "{$moduleName} module for Zoosper CMS.",
    "type": "zoosper-module",
    "license": "MIT",
    "require": {
        "php": "^8.5",
        "zoosper/core": "*@dev"
    },
    "require-dev": {
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin": "^3.0",
        "phpunit/phpunit": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "{$namespaceEscaped}": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "{$namespaceEscaped}Tests\\\\": "tests/"
        }
    },
    "extra": {
        "zoosper": {
            "module": "module.php",
            "name": "{$moduleName}"
        }
    }
}
JSON,
            'module.php' => <<<PHP
<?php

declare(strict_types=1);

return [
    'name' => '{$moduleName}',
    'enabled' => true,
    'version' => '0.1.0',
    'sort_order' => 100,
];
PHP,
            'config/services.php' => <<<PHP
<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;

return [
    // Register module services here.
];
PHP,
            'config/db_schema.php' => <<<PHP
<?php

declare(strict_types=1);

return [
    'tables' => [
        // Declare module-owned tables here.
    ],
];
PHP,
            'config/admin_routes.php' => <<<PHP
<?php

declare(strict_types=1);

return [
    // Declare admin routes here when the module adds admin UI.
];
PHP,
            'config/api_routes.php' => <<<PHP
<?php

declare(strict_types=1);

return [
    // Declare API routes here when the module exposes API endpoints.
];
PHP,
            'src/.gitkeep' => '',
            "tests/Unit/{$classPrefix}PackageTest.php" => <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}Tests\Unit;

test('{$moduleName} package metadata is discoverable', function () {
    \$packageRoot = dirname(__DIR__, 2);
    \$module = require \$packageRoot . '/module.php';
    \$composer = json_decode((string) file_get_contents(\$packageRoot . '/composer.json'), true);

    expect(\$module['name'])->toBe('{$moduleName}');
    expect(\$composer['type'])->toBe('zoosper-module');
    expect(\$composer['extra']['zoosper']['module'])->toBe('module.php');
});
PHP,
            'phpunit.xml.dist' => <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
</phpunit>
XML,
            'README.md' => <<<MD
# {$moduleName}

Composer package module for Zoosper CMS.

## Development from the Zoosper root

```bash
PHP=php8.5 composer dump-autoload
vendor/bin/pest packages/{$identity['package_dir']}/tests/Unit
```

## Module files

```text
module.php
config/
src/
tests/
```
MD,
            '.gitignore' => "/vendor/\n/.phpunit.cache/\n/coverage/\n/composer.lock\n.DS_Store\n",
            '.github/workflows/tests.yml' => <<<YML
name: Package tests

on:
  pull_request:
  push:
    branches:
      - main
      - dev

jobs:
  unit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/pest --testsuite=Unit
YML,
        ];
    }
}
