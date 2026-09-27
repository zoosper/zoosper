<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleRegistry;

function relocatableManifestFixture(string $suffix): string
{
    $root = sys_get_temp_dir() . '/zoosper-relocatable-' . $suffix . '-' . bin2hex(random_bytes(4));
    $module = $root . '/app/acme-example';
    mkdir($module . '/config', 0775, true);
    file_put_contents($root . '/composer.lock', '{"packages":[]}');
    file_put_contents($module . '/module.php', "<?php\nreturn ['name' => 'acme-example'];\n");
    file_put_contents($module . '/config/services.php', "<?php\nreturn [];\n");
    file_put_contents($module . '/config/admin_routes.php', "<?php\nreturn [];\n");
    file_put_contents($module . '/config/api_routes.php', "<?php\nreturn [];\n");

    return $root;
}

it('writes deterministic root-relative compiled cache content across different roots', function (): void {
    $one = relocatableManifestFixture('one');
    $two = relocatableManifestFixture('two');
    touch($one . '/app/acme-example/module.php', 1700000000);
    touch($two . '/app/acme-example/module.php', 1700000000);

    (new ModuleManifestCompiler($one))->compile();
    (new ModuleManifestCompiler($two))->compile();

    foreach (['modules.php', 'services_compiled.php', 'routes_admin_compiled.php', 'routes_api_compiled.php'] as $cache) {
        $first = (string) file_get_contents($one . '/var/cache/' . $cache);
        $second = (string) file_get_contents($two . '/var/cache/' . $cache);
        expect($first)->toBe($second)
            ->not->toContain($one)
            ->not->toContain($two)
            ->not->toContain('Generated:');
    }

    expect((new ModuleRegistry($one))->enabledModules()[0]->path)->toBe($one . '/app/acme-example');
    expect((new ModuleRegistry($two))->enabledModules()[0]->path)->toBe($two . '/app/acme-example');

    exec('rm -rf ' . escapeshellarg($one));
    exec('rm -rf ' . escapeshellarg($two));
});
