<?php

declare(strict_types=1);

namespace Zoosper\Settings\Write;

use InvalidArgumentException;
use Zoosper\Settings\Persistence\ScopedSettingStoreInterface;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Settings\Definition\SettingsSection;

/** Removes one declared editable override so normal inheritance resumes. */
final readonly class ScopedSettingClearer
{
    public function __construct(private ScopedSettingStoreInterface $store)
    {
    }

    public function clear(SettingsSection $section, string $path, ScopeType $scope, ?string $scopeKey): void
    {
        foreach ($section->settings as $definition) {
            if ($definition->path !== $path) {
                continue;
            }
            if ($definition->readOnly || $definition->secret) {
                throw new SettingValidationException([$path => 'This setting cannot be reset here.']);
            }
            $this->store->clear($path, $scope, $scopeKey);
            return;
        }

        throw new InvalidArgumentException("Unknown setting path for section '{$section->id}': {$path}");
    }
}










