<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Config;

use Zoosper\Core\Config\ConfigRepository;

test('fromArray exposes values via dot notation', function () {
    $config = ConfigRepository::fromArray([
        'logging' => ['default_file' => 'system.log'],
    ]);

    expect($config->get('logging.default_file'))->toBe('system.log');
});

test('get returns the default for a missing key', function () {
    $config = ConfigRepository::fromArray([]);

    expect($config->get('missing.key', 'fallback'))->toBe('fallback');
});

test('array returns an empty array for a missing key', function () {
    $config = ConfigRepository::fromArray([]);

    expect($config->array('missing'))->toBe([]);
});

test('fromPath loads PHP configuration files and ignores missing directories', function (): void {
    $directory = sys_get_temp_dir() . '/zoosper-config-repository-' . bin2hex(random_bytes(6));
    mkdir($directory, 0775, true);
    file_put_contents($directory . '/app.php', "<?php return ['name' => 'Zoosper'];");
    file_put_contents($directory . '/database.php', "<?php return ['default' => 'sqlite'];");

    expect(ConfigRepository::fromPath($directory)->get('app.name'))->toBe('Zoosper')
        ->and(ConfigRepository::fromPath($directory)->get('database.default'))->toBe('sqlite')
        ->and(ConfigRepository::fromPath($directory . '/missing')->get('app.name', 'fallback'))->toBe('fallback');
});
