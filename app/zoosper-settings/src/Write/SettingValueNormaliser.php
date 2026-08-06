<?php

declare(strict_types=1);

namespace Zoosper\Settings\Write;

use Zoosper\Settings\Definition\SettingDefinition;

/** Converts browser input into the canonical string stored by ScopeConfigRepository. */
final readonly class SettingValueNormaliser
{
    public function normalise(SettingDefinition $definition, mixed $input): string
    {
        if ($definition->readOnly || $definition->secret) {
            throw new SettingValidationException([$definition->path => 'This setting cannot be edited here.']);
        }
        if (is_array($input) && $definition->type !== 'multiselect') {
            throw new SettingValidationException([$definition->path => 'A scalar value is required.']);
        }

        $value = is_scalar($input) || $input === null ? trim((string) $input) : '';
        return match ($definition->type) {
            'text', 'textarea' => $value,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false
                ? $value : throw new SettingValidationException([$definition->path => 'Enter a valid email address.']),
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false
                ? $value : throw new SettingValidationException([$definition->path => 'Enter a valid absolute URL.']),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false
                ? (string) (int) $value : throw new SettingValidationException([$definition->path => 'Enter a whole number.']),
            'decimal' => is_numeric($value)
                ? (string) (float) $value : throw new SettingValidationException([$definition->path => 'Enter a number.']),
            'boolean' => $this->boolean($definition, $value),
            'select' => $this->option($definition, $value),
            'multiselect' => $this->multiple($definition, $input),
            default => throw new SettingValidationException([$definition->path => 'This setting type is not editable.']),
        };
    }

    private function boolean(SettingDefinition $definition, string $value): string
    {
        return match (strtolower($value)) {
            '1', 'true', 'on', 'yes' => '1',
            '0', 'false', 'off', 'no', '' => '0',
            default => throw new SettingValidationException([$definition->path => 'Choose enabled or disabled.']),
        };
    }

    private function option(SettingDefinition $definition, string $value): string
    {
        if (!in_array($value, $definition->options, true)) {
            throw new SettingValidationException([$definition->path => 'Choose an available option.']);
        }
        return $value;
    }

    private function multiple(SettingDefinition $definition, mixed $input): string
    {
        $values = is_array($input) ? array_values(array_unique(array_map('strval', $input))) : [];
        foreach ($values as $value) {
            if (!in_array($value, $definition->options, true)) {
                throw new SettingValidationException([$definition->path => 'Choose only available options.']);
            }
        }
        sort($values);
        return json_encode($values, JSON_THROW_ON_ERROR);
    }
}
