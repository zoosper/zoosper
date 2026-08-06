<?php

declare(strict_types=1);

namespace Zoosper\Settings\Write;

use RuntimeException;

/** Validation failure keyed by the declared setting path. */
final class SettingValidationException extends RuntimeException
{
    /** @param array<string, string> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('One or more settings are invalid.');
    }
}
