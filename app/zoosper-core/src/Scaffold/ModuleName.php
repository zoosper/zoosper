<?php

declare(strict_types=1);

namespace Zoosper\Core\Scaffold;

use Zoosper\Core\Exception\ZoosperException;

/**
 * Normalised representation of a Zoosper module name.
 *
 * Accepted input format: Vendor_Module, e.g. Acme_Blog.
 */
final readonly class ModuleName
{
    private function __construct(
        public string $raw,
        public string $vendor,
        public string $module,
        public string $namespace,
        public string $folderName,
    ) {
    }

    public static function fromInput(string $input): self
    {
        $input = trim($input);
        if (preg_match('/^[A-Za-z][A-Za-z0-9]*_[A-Za-z][A-Za-z0-9]*$/', $input) !== 1) {
            throw new ZoosperException(
                message: 'Invalid module name: ' . ($input === '' ? '(empty)' : $input),
                context: 'Zoosper module names use the Vendor_Module format, for example Acme_Blog.',
                suggestion: 'Run `php bin/zoosper make:module Acme_Blog`. Use letters and numbers only on both sides of the underscore.',
                docsUrl: 'docs/contributor/module-generator.md',
                details: ['input' => $input],
            );
        }

        [$vendor, $module] = explode('_', $input, 2);
        $vendor = self::studly($vendor);
        $module = self::studly($module);

        return new self(
            raw: $vendor . '_' . $module,
            vendor: $vendor,
            module: $module,
            namespace: $vendor . '\\' . $module,
            folderName: strtolower(self::kebab($vendor) . '-' . self::kebab($module)),
        );
    }

    private static function studly(string $value): string
    {
        return ucfirst(strtolower($value));
    }

    private static function kebab(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
    }
}
