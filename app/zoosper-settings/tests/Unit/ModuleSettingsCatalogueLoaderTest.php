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
    'settings' => ['enabled' => ['path' => 'acme.enabled', 'label' => 'Enabled', 'type' => 'boolean']],
], [
    'id' => 'acme.grouped', 'label' => 'Grouped',
    'groups' => [['id' => 'main', 'label' => 'Main', 'settings' => [
        'second' => ['path' => 'acme.second', 'label' => 'Second', 'sort_order' => 20],
        'first' => ['path' => 'acme.first', 'label' => 'First', 'sort_order' => 10],
    ]]],
]];
PHP);

    $catalogue = (new ModuleSettingsCatalogueLoader(new ModuleRegistry($root)))->load();
    expect($catalogue->all())->toHaveCount(2)
        ->and($catalogue->all()[0]->module)->toBe('acme-settings')
        ->and($catalogue->all()[1]->settings[0]->type)->toBe('boolean')
        ->and(array_map(static fn ($setting) => $setting->path, $catalogue->all()[0]->settings))
        ->toBe(['acme.first', 'acme.second']);
    exec('rm -rf ' . escapeshellarg($root));
});










