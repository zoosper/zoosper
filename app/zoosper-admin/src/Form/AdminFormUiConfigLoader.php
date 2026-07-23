<?php

declare(strict_types=1);


/**
 * PHASE_140DF_ADMIN_UI_CONFIG_LAYERED_LOADER
 * Runtime migration marker: approved to use ConfigFileLayeredLoader
 * for module-default plus root-override config resolution.
 */
namespace Zoosper\Admin\Form;


use Zoosper\Core\Config\ConfigFileLayeredLoader;
use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class AdminFormUiConfigLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    public function load(string $handle): AdminFormDefinition
    {
        /** @var array<string, array<string, mixed>> $fields */
        $fields = [];
        /** @var list<string> $removed */
        $removed = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_ui.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new RuntimeException('Admin UI config must return an array: ' . $file);
            }

            $fragment = $config[$handle] ?? null;
            if (!is_array($fragment)) {
                continue;
            }

            foreach (($fragment['remove'] ?? []) as $fieldName) {
                $removed[] = (string) $fieldName;
                unset($fields[(string) $fieldName]);
            }

            foreach (($fragment['fields'] ?? []) as $name => $fieldConfig) {
                if (in_array((string) $name, $removed, true) || !is_array($fieldConfig)) {
                    continue;
                }
                $fields[(string) $name] = array_replace($fields[(string) $name] ?? [], $fieldConfig);
            }

            foreach (($fragment['replace'] ?? []) as $name => $fieldConfig) {
                if (!is_array($fieldConfig)) {
                    continue;
                }
                $fields[(string) $name] = $fieldConfig;
                $removed = array_values(array_diff($removed, [(string) $name]));
            }

            foreach (($fragment['inject'] ?? []) as $position => $injectedFields) {
                if (!is_array($injectedFields)) {
                    continue;
                }
                foreach ($injectedFields as $name => $fieldConfig) {
                    if (is_array($fieldConfig) && !in_array((string) $name, $removed, true)) {
                        $fields[(string) $name] = $fieldConfig + ['position' => (string) $position];
                    }
                }
            }
        }

        $objects = [];
        foreach ($fields as $name => $fieldConfig) {
            if (!in_array($name, $removed, true)) {
                $objects[] = AdminFormField::fromConfig($name, $fieldConfig);
            }
        }

        usort($objects, static fn (AdminFormField $a, AdminFormField $b): int => [$a->sortOrder, $a->label] <=> [$b->sortOrder, $b->label]);

        return new AdminFormDefinition($handle, $objects);
    }
}
