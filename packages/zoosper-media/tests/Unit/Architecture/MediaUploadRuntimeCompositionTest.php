<?php

declare(strict_types=1);

it('injects one configured upload service into both production controllers', function (): void {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');
    $controllers = (string) file_get_contents($root . '/packages/zoosper-media/config/controllers.php');

    expect($services)->toContain('MediaUploadService::class =>')
        ->toContain('cleanup: $services->get(MediaStoredFileCleanupService::class)')
        ->and($controllers)->toContain('uploads: $services->get(MediaUploadService::class)')
        ->not->toContain('MediaUploadValidator::class')
        ->not->toContain('MediaStorage::class');
});

it('forbids private upload-service reconstruction in media controllers', function (): void {
    $root = dirname(__DIR__, 5);
    foreach (['MediaAdminController.php', 'MediaEditorJsUploadController.php'] as $file) {
        $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Controller/' . $file);
        expect($source)->toContain('private MediaUploadService $uploads')
            ->not->toContain('new MediaUploadService(')
            ->not->toContain('MediaUploadService $uploads = null');
    }
});











