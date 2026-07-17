<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

use Zoosper\Core\Exception\ZoosperException;

/**
 * Creates a module-owned console command skeleton and wires config/console.php.
 *
 * This is intentionally conservative: it writes a dependency-free command class
 * and updates only the target module's own files. Commands that need services can
 * later move dependencies into the constructor and be registered in the module's
 * config/services.php.
 */
final readonly class ConsoleCommandScaffolder
{
    public function __construct(private string $basePath)
    {
    }

    public function scaffold(string $moduleInput, string $classInput, string $commandName, string $description = ''): ConsoleCommandScaffoldResult
    {
        $module = ModuleName::fromInput($moduleInput);
        $className = $this->normaliseClassName($classInput);
        $commandName = $this->normaliseCommandName($commandName);
        $description = trim($description) !== '' ? trim($description) : 'Run ' . $commandName . '.';

        $modulePath = $this->basePath . '/app/' . $module->folderName;
        if (!is_dir($modulePath)) {
            throw new ZoosperException(
                message: 'Module does not exist: ' . $module->raw,
                context: 'The console command generator writes into an existing module directory, but the target module was not found: ' . $modulePath,
                suggestion: 'Create the module first with `php bin/zoosper make:module ' . $module->raw . '`, then run `php bin/zoosper make:command ' . $module->raw . ' ' . $className . ' --name=' . $commandName . '`.',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['module' => $module->raw, 'path' => $modulePath],
            );
        }

        $commandRelative = 'src/Console/' . $className . '.php';
        $commandPath = $modulePath . '/' . $commandRelative;
        if (is_file($commandPath)) {
            throw new ZoosperException(
                message: 'Console command class already exists: ' . $className,
                context: 'The target command file already exists: ' . $commandPath,
                suggestion: 'Choose a different command class name or edit the existing file directly.',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['module' => $module->raw, 'file' => $commandPath],
            );
        }

        if (!is_dir(dirname($commandPath))) {
            mkdir(dirname($commandPath), 0775, true);
        }

        $fqcn = $module->namespace . '\\Console\\' . $className;
        file_put_contents($commandPath, $this->commandClass($module->namespace, $className, $commandName, $description));

        $consoleConfigRelative = 'config/console.php';
        $consoleConfigPath = $modulePath . '/' . $consoleConfigRelative;
        $this->wireConsoleConfig($consoleConfigPath, $fqcn, $className);

        return new ConsoleCommandScaffoldResult(
            moduleName: $module->raw,
            commandClass: $fqcn,
            commandName: $commandName,
            commandFile: 'app/' . $module->folderName . '/' . $commandRelative,
            consoleConfigFile: 'app/' . $module->folderName . '/' . $consoleConfigRelative,
            createdFiles: [
                'app/' . $module->folderName . '/' . $commandRelative,
                'app/' . $module->folderName . '/' . $consoleConfigRelative,
            ],
        );
    }

    private function normaliseClassName(string $input): string
    {
        $input = trim($input);
        if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $input) !== 1) {
            throw new ZoosperException(
                message: 'Invalid console command class name: ' . ($input === '' ? '(empty)' : $input),
                context: 'Console command class names must be simple PHP class names without namespace separators.',
                suggestion: 'Use a class name such as ReindexPostsCommand or ExportFeedCommand.',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['input' => $input],
            );
        }

        return ucfirst($input);
    }

    private function normaliseCommandName(string $input): string
    {
        $input = strtolower(trim($input));
        if (preg_match('/^[a-z][a-z0-9-]*(?::[a-z][a-z0-9-]*)+$/', $input) !== 1) {
            throw new ZoosperException(
                message: 'Invalid console command name: ' . ($input === '' ? '(empty)' : $input),
                context: 'Console command names should be colon-separated and vendor/module-prefixed.',
                suggestion: 'Use a name such as blog:posts:reindex or catalog:feeds:export.',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['input' => $input],
            );
        }

        return $input;
    }

    private function commandClass(string $namespaceRoot, string $className, string $commandName, string $description): string
    {
        $namespace = $namespaceRoot . '\\Console';
        $safeCommandName = var_export($commandName, true);
        $safeDescription = var_export($description, true);

        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;

/**
 * Module-owned console command scaffold.
 *
 * Keep CLI output free of secrets, OTPs, TOTP secrets, recovery-code plaintext,
 * reset tokens, SMTP passwords, payment data and customer-private values.
 */
final readonly class {$className} implements ConsoleCommandInterface
{
    public function name(): string
    {
        return {$safeCommandName};
    }

    public function description(): string
    {
        return {$safeDescription};
    }

    public function run(array \$args, ConsoleOutput \$output): int
    {
        \$output->writeln('Command ' . \$this->name() . ' is ready.');

        return 0;
    }
}
PHP;
    }

    private function wireConsoleConfig(string $file, string $fqcn, string $className): void
    {
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        if (!is_file($file)) {
            file_put_contents($file, $this->newConsoleConfig($fqcn, $className));
            return;
        }

        $source = (string) file_get_contents($file);
        if (str_contains($source, $fqcn) || str_contains($source, $className . '::class')) {
            return;
        }

        if (!str_contains($source, 'return [')) {
            throw new ZoosperException(
                message: 'Unable to update console command config: ' . $file,
                context: 'The console config exists, but it does not contain a `return [` command list.',
                suggestion: 'Update config/console.php manually to return a list of ConsoleCommandInterface class strings.',
                docsUrl: 'docs/contributor/module-console-commands.md',
                details: ['file' => $file],
            );
        }

        if (!str_contains($source, 'use ' . $fqcn . ';')) {
            $source = preg_replace('/(declare\(strict_types=1\);\s*)/m', "$1\nuse {$fqcn};\n", $source, 1) ?? $source;
        }

        $source = preg_replace('/return \[\s*/m', "return [\n    {$className}::class,\n", $source, 1) ?? $source;
        file_put_contents($file, $source);
    }

    private function newConsoleConfig(string $fqcn, string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use {$fqcn};

return [
    {$className}::class,
];
PHP;
    }
}
