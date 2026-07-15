<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

use Zoosper\Core\Exception\ZoosperException;

/**
 * Creates a minimal, modern Zoosper module skeleton.
 *
 * The generator is intentionally conservative: it creates files but does not edit
 * composer.json automatically yet. The generated README includes the PSR-4
 * autoload snippet so developers can review and add it explicitly.
 */
final readonly class ModuleScaffolder
{
    public function __construct(private string $basePath)
    {
    }

    public function scaffold(string $input): ModuleScaffoldResult
    {
        $name = ModuleName::fromInput($input);
        $modulePath = $this->basePath . '/app/' . $name->folderName;

        if (is_dir($modulePath)) {
            throw new ZoosperException(
                message: 'Module already exists: ' . $name->raw,
                context: 'The target module directory already exists: ' . $modulePath,
                suggestion: 'Choose a different module name or remove the existing directory before scaffolding.',
                docsUrl: 'docs/contributor/module-generator.md',
                details: ['module' => $name->raw, 'path' => $modulePath],
            );
        }

        $files = $this->files($name);
        $created = [];

        foreach ($files as $relative => $contents) {
            $path = $modulePath . '/' . $relative;
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
            file_put_contents($path, $contents);
            $created[] = 'app/' . $name->folderName . '/' . $relative;
        }

        return new ModuleScaffoldResult($name->raw, $name->namespace, 'app/' . $name->folderName, $created);
    }

    /** @return array<string, string> */
    private function files(ModuleName $name): array
    {
        $ns = $name->namespace;
        $raw = $name->raw;
        $folder = $name->folderName;

        return [
            'module.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'name' => '{$raw}',\n    'enabled' => true,\n    'version' => '0.1.0',\n];\n",
            'config/services.php' => "<?php\n\ndeclare(strict_types=1);\n\nuse Zoosper\\Core\\Container\\ServiceContainer;\n\nreturn [\n    // {$raw} service factories go here.\n];\n",
            'config/controllers.php' => <<<'PHP'
            <?php
            
            declare(strict_types=1);
            
            use Zoosper\Core\Container\ServiceContainer;
            
            return [
                // ControllerClass::class => static fn (ServiceContainer $services): ControllerClass => new ControllerClass(...),
            ];
            PHP,
            'config/admin_routes.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    // ['GET', '/example', ControllerClass::class, 'index'],\n];\n",
            'config/api_routes.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    // ['GET', '/example', ControllerClass::class, 'index'],\n];\n",
            'config/acl.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'permissions' => [\n        strtolower('{$raw}') . '.manage' => '{$raw}: manage',\n    ],\n];\n",
            'config/db_schema.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'tables' => [\n        // 'example_table' => [\n        //     'columns' => [\n        //         'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],\n        //     ],\n        // ],\n    ],\n];\n",
            'config/events.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    // 'page.published' => [ExampleListener::class],\n];\n",
            'config/logging.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'service' => 'logger.' . strtolower('{$raw}'),\n    'file' => strtolower('{$folder}') . '.log',\n];\n",
            'resources/views/.gitkeep' => "",
            'i18n/en_AU.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    // 'Hello' => 'Hello',\n];\n",
            'tests/Pest.php' => "<?php\n\ndeclare(strict_types=1);\n\nuses(\\Zoosper\\Core\\Testing\\TestCase::class)->in(__DIR__);\n",
            'tests/Unit/ModuleBootstrapTest.php' => "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$ns}\\Tests\\Unit;\n\ntest('module scaffold exists', function () {\n    expect(is_file(dirname(__DIR__, 2) . '/module.php'))->toBeTrue();\n});\n",
            'src/.gitkeep' => "",
            'README.md' => "# {$raw}\n\nGenerated Zoosper module scaffold.\n\n## Autoload\n\nAdd this to `composer.json` if you create PHP classes under `src/`:\n\n```json\n\"{$ns}\\\\\": \"app/{$folder}/src/\"\n```\n\nThen run:\n\n```bash\ncomposer dump-autoload\n```\n\n## Events\n\nUse `config/events.php` to observe application events without touching core.\n",
        ];
    }
}
