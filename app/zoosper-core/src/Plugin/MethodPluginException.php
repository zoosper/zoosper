<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use RuntimeException;

/**
 * Descriptive exception for method plugin configuration/runtime diagnostics.
 */
final class MethodPluginException extends RuntimeException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $message,
        public readonly string $suggestion = '',
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @param array<string, mixed> $details
     */
    public static function invalidConfigEntry(string $reason, array $details = []): self
    {
        return new self(
            message: 'Invalid method plugin config entry: ' . $reason,
            suggestion: 'Expected an array entry with non-empty subject, method, and plugin class-string values.',
            details: $details,
        );
    }

    public static function pluginClassMissing(string $pluginClass): self
    {
        return new self(
            message: 'Method plugin class does not exist: ' . $pluginClass,
            suggestion: 'Check the plugin class namespace and composer/module autoload mapping.',
            details: ['pluginClass' => $pluginClass],
        );
    }

    public static function pluginInterfaceMismatch(string $pluginClass): self
    {
        return new self(
            message: 'Method plugin class must implement ' . MethodInterceptorInterface::class . ': ' . $pluginClass,
            suggestion: 'Implement MethodInterceptorInterface or remove/disable the plugin config entry.',
            details: ['pluginClass' => $pluginClass, 'expectedInterface' => MethodInterceptorInterface::class],
        );
    }
}
