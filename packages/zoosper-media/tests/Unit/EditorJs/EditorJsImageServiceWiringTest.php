<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Editor\EditorImageBlockSanitizerInterface;
use Zoosper\Core\Editor\EditorImageBlockValidatorInterface;
use Zoosper\Core\Editor\EditorImageToolConfigInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

it('resolves editor image contracts through the media service manifest', function (): void {
    $container = new ServiceContainer();
    $container->set(AdminUrlGenerator::class, new AdminUrlGenerator(ConfigRepository::fromArray([
        'admin' => ['base_path' => '/control'],
    ])));
    $factories = require dirname(__DIR__, 3) . '/config/services.php';
    foreach ($factories as $id => $factory) {
        $container->factory($id, $factory);
    }

    $sanitizer = $container->get(EditorImageBlockSanitizerInterface::class);
    $validator = $container->get(EditorImageBlockValidatorInterface::class);
    $config = $container->get(EditorImageToolConfigInterface::class);

    expect($sanitizer)->toBeInstanceOf(EditorJsImageBlockSanitizer::class)
        ->and($validator)->toBe($sanitizer)
        ->and($config)->toBeInstanceOf(EditorJsImageToolConfig::class)
        ->and($config->toArray('token')['endpoints']['byFile'])->toBe('/control/media/editorjs/upload')
        ->and($sanitizer->sanitise(['file' => ['url' => 'https://example.com/image.png']]))->toBeNull()
        ->and($validator->validate(['file' => ['url' => 'https://example.com/image.png']]))->not->toBeEmpty();
});