<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use Zoosper\Core\Module\ModuleRegistry;

function composerIdentityAuthorityFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-composer-identity-' . bin2hex(random_bytes(6));
    $modulePath = $root . '/app/acme-content';
    mkdir($modulePath, 0775, true);

    file_put_contents(
        $modulePath . '/composer.json',
        json_encode([
            'name' => 'acme/content',
            'type' => 'zoosper-module',
            'version' => '0.4.0',
            'extra' => ['marko' => ['module' => true]],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
    );
    file_put_contents(
        $modulePath . '/module.php',
        "<?php return ['name' => 'Wrong_LegacyName', 'version' => '9.9.9', 'enabled' => true];",
    );

    return $root;
}

test('Composer package identity overrides conflicting module metadata', function (): void {
    $modules = (new ModuleRegistry(composerIdentityAuthorityFixture()))->discoverModulesLive();

    expect($modules)->toHaveCount(1);
    expect($modules[0]->name)->toBe('acme-content');
    expect($modules[0]->version)->toBe('0.4.0');
});
