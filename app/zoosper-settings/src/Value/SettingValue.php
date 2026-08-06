<?php

declare(strict_types=1);

namespace Zoosper\Settings\Value;

use InvalidArgumentException;

/** Safe presentation value plus its winning configuration source. */
final readonly class SettingValue
{
    public function __construct(
        public string $path,
        public mixed $value,
        public string $source,
        public bool $readOnly,
        public bool $secret = false,
        public ?string $explanation = null,
    ) {
        if (!in_array($source, ['default', 'project', 'environment', 'database', 'inherited', 'unset'], true)) {
            throw new InvalidArgumentException("Unsupported setting source '{$source}' for: {$path}");
        }
        if ($secret && $value !== null) {
            throw new InvalidArgumentException("Secret values must never be exposed for: {$path}");
        }
    }
}
