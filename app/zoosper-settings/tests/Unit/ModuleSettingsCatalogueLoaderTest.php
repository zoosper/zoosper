<?php

declare(strict_types=1);

use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Core\Module\ModuleRegistry;

it('discovers module-owned admin settings metadata', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-settings-catalogue-' . bin2hex(random_bytes(5));
    $module = $root . '/app/acme-settings';
    mkdir($module . '/config', 0775, true);
    file_put_contents($module . '/module.php', "<?php return ['name' => 'acme-settings'];");
    file_put_contents($module . '/config/admin_settings.php', <<<'PHP'
<?php
return [[
    'id' => 'acme.general', 'label' => 'Acme', 'category' => 'general',
    'settings' => [['path' => 'acme.enabled', 'label' => 'Enabled', 'type' => 'boolean']],
]];
PHP);

    $catalogue = (new ModuleSettingsCatalogueLoader(new ModuleRegistry($root)))->load();
    expect($catalogue->all())->toHaveCount(1)
        ->and($catalogue->all()[0]->module)->toBe('acme-settings')
        ->and($catalogue->all()[0]->settings[0]->type)->toBe('boolean');
    exec('rm -rf ' . escapeshellarg($root));
});
