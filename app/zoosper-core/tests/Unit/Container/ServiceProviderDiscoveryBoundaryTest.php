<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Core\Module\Module;
use Zoosper\Core\Module\ModuleRegistry;

it('preserves service discovery flag truthiness and filesystem fallback', function (string $kind, mixed $flag, bool $setFlag, bool $exists, bool $expected): void {
    $root = sys_get_temp_dir() . '/zoosper-service-discovery-' . bin2hex(random_bytes(6));
    mkdir($root . '/config', 0775, true);
    $file = $root . '/config/' . $kind . '.php';
    if ($exists) {
        $contents = $kind === 'services'
            ? "<?php return ['example' => (object) ['value' => 'loaded']];"
            : "<?php return ['example' => static function (\$container, \$service) { \$service->value = 'loaded'; return \$service; }];";
        file_put_contents($file, $contents);
    }

    try {
        $registry = new ModuleRegistry($root);
        $discovery = $setFlag ? [$kind => $flag] : [];
        (new ReflectionProperty($registry, 'cachedModules'))->setValue($registry, [new Module('example', $root, discovery: $discovery)]);
        $services = new ServiceContainer();
        $services->set('example', (object) ['value' => 'original']);
        (new ServiceProviderLoader($registry, $services))->register();

        expect($services->get('example')->value)->toBe($expected ? 'loaded' : 'original');
    } finally {
        if ($exists) {
            unlink($file);
        }
        rmdir($root . '/config');
        rmdir($root);
    }
})->with(['services', 'service_decorators'])->with([
    'missing present' => [null, false, true, true],
    'missing absent' => [null, false, false, false],
    'null' => [null, true, true, true],
    'false' => [false, true, true, false],
    'true' => [true, true, true, true],
    'integer zero' => [0, true, true, false],
    'integer one' => [1, true, true, true],
    'string zero' => ['0', true, true, false],
    'string one' => ['1', true, true, true],
]);