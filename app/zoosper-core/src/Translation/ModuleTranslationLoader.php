<?php

declare(strict_types=1);

namespace Zoosper\Core\Translation;

use Zoosper\Core\Module\ModuleRegistry;

final readonly class ModuleTranslationLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    /** @return array<string, string> */
    public function load(string $locale = 'en_GB'): array
    {
        $messages = [];
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('translations/' . $locale . '.php');
            if (!is_file($file)) {
                continue;
            }
            $moduleMessages = require $file;
            if (is_array($moduleMessages)) {
                $messages = array_merge($messages, $moduleMessages);
            }
        }
        return $messages;
    }
}
