<?php

declare(strict_types=1);

namespace Zoosper\Core\Composer;

/**
 * Derived Composer package identity for a Zoosper module.
 */
final readonly class ModulePackageIdentity
{
    public function __construct(
        public string $moduleName,
        public string $packageName,
        public string $namespace,
    ) {
    }

    /** @param array<string, mixed> $module */
    public static function fromModule(array $module, string $folderName): ?self
    {
        $name = trim((string) ($module['name'] ?? ''));
        if ($name === '') {
            $name = $folderName;
        }

        return self::fromName($name, $folderName);
    }

    public static function fromName(string $name, string $folderName = ''): ?self
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z][A-Za-z0-9]*_[A-Za-z][A-Za-z0-9]*$/', $name) === 1) {
            [$vendor, $module] = explode('_', $name, 2);

            return new self(
                moduleName: self::studly($vendor) . '_' . self::studly($module),
                packageName: strtolower($vendor) . '/' . self::kebab($module),
                namespace: self::studly($vendor) . '\\' . self::studly($module) . '\\',
            );
        }

        $source = strtolower($name);
        if (!str_contains($source, '-') && $folderName !== '') {
            $source = strtolower($folderName);
        }

        if (preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)+$/', $source) !== 1) {
            return null;
        }

        $parts = explode('-', $source);
        $vendor = array_shift($parts);
        if ($vendor === null || $parts === []) {
            return null;
        }

        $moduleStudly = implode('', array_map(self::studly(...), $parts));
        $moduleKebab = implode('-', $parts);

        return new self(
            moduleName: self::studly($vendor) . '_' . $moduleStudly,
            packageName: $vendor . '/' . $moduleKebab,
            namespace: self::studly($vendor) . '\\' . $moduleStudly . '\\',
        );
    }

    private static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', strtolower($value))));
    }

    private static function kebab(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
    }
}
