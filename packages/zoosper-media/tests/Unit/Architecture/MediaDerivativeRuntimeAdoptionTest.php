<?php

declare(strict_types=1);

it('wires enabled GD derivatives into the canonical upload service', function (): void {
    $root = dirname(__DIR__, 3);
    $services = (string) file_get_contents($root . '/config/services.php');
    $upload = (string) file_get_contents($root . '/src/Service/MediaUploadService.php');
    expect($services)
        ->toContain('MediaProcessorInterface::class')
        ->toContain('new GdMediaProcessor(')
        ->toContain('new MediaUploadDerivativePolicy(true)')
        ->toContain('derivatives: $services->get(MediaUploadDerivativeDispatcher::class)')
        ->and($upload)
        ->toContain('!$derivativeResult->successful')
        ->toContain('throw new \RuntimeException');
});











