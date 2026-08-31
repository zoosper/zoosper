<?php

declare(strict_types=1);

namespace Zoosper\Settings\Write;

use Zoosper\Settings\Persistence\ScopedSettingStoreInterface;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Settings\Definition\SettingsSection;

/** Validates a complete declared section before atomically writing any value. */
final readonly class SectionSettingsWriter
{
    public function __construct(
        private ScopedSettingStoreInterface $store,
        private SettingValueNormaliser $normaliser,
    ) {
    }

    /** @param array<string, mixed> $submitted */
    public function write(SettingsSection $section, ScopeType $scope, ?string $scopeKey, array $submitted): void
    {
        $normalised = [];
        $errors = [];
        foreach ($section->settings as $definition) {
            if ($definition->readOnly || $definition->secret) {
                continue;
            }
            if (!array_key_exists($definition->path, $submitted)) {
                $errors[$definition->path] = 'A value is required.';
                continue;
            }
            try {
                $normalised[$definition->path] = $this->normaliser->normalise($definition, $submitted[$definition->path]);
            } catch (SettingValidationException $exception) {
                $errors += $exception->errors;
            }
        }
        if ($errors !== []) {
            throw new SettingValidationException($errors);
        }

        $this->store->writeMany($normalised, $scope, $scopeKey);
    }
}










