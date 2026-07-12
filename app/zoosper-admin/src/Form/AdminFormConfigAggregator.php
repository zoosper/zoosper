<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

/**
 * Aggregates admin form provider configuration from app and third-party modules.
 *
 * This allows a module to ship `config/admin_forms.php` and contribute sections
 * to a stable form handle without editing a core controller or root config file.
 */
final readonly class AdminFormConfigAggregator
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $rootConfig Runtime/root config already loaded
     *                                         by ConfigRepository, if available.
     *
     * @return array{forms: array<string, list<class-string<AdminFormSectionProviderInterface>>>}
     */
    public function aggregate(array $rootConfig = []): array
    {
        $forms = [];

        foreach ($this->configFiles() as $configFile) {
            $forms = $this->mergeForms($forms, $this->readConfigFile($configFile));
        }

        $forms = $this->mergeForms($forms, $rootConfig);

        return ['forms' => $forms];
    }

    /** @return list<string> */
    private function configFiles(): array
    {
        $patterns = [
            $this->basePath . '/app/*/config/admin_forms.php',
            $this->basePath . '/modules/*/config/admin_forms.php',
            $this->basePath . '/modules/*/*/config/admin_forms.php',
            $this->basePath . '/vendor/*/*/config/admin_forms.php',
            $this->basePath . '/config/admin_forms.php',
        ];

        $files = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file)) {
                    $files[] = $file;
                }
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    /** @return array<string, mixed> */
    private function readConfigFile(string $file): array
    {
        $config = require $file;

        return is_array($config) ? $config : [];
    }

    /**
     * @param array<string, list<class-string<AdminFormSectionProviderInterface>>> $forms
     * @param array<string, mixed> $config
     *
     * @return array<string, list<class-string<AdminFormSectionProviderInterface>>>
     */
    private function mergeForms(array $forms, array $config): array
    {
        $incoming = isset($config['forms']) && is_array($config['forms']) ? $config['forms'] : $config;

        foreach ($incoming as $formHandle => $providerClasses) {
            if (!is_string($formHandle) || !is_array($providerClasses)) {
                continue;
            }

            foreach ($providerClasses as $providerClass) {
                if (!is_string($providerClass) || $providerClass === '') {
                    continue;
                }

                if (!isset($forms[$formHandle])) {
                    $forms[$formHandle] = [];
                }

                if (!in_array($providerClass, $forms[$formHandle], true)) {
                    $forms[$formHandle][] = $providerClass;
                }
            }
        }

        return $forms;
    }
}
