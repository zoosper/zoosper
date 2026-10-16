<?php

declare(strict_types=1);

it('locks selected runtime APIs to declarative registration or direct runtime consumers', function (): void {
    $root = dirname(__DIR__, 5);
    $sources = [
        'app/zoosper-global-announcements/src/Announcement/AdminAnnouncement.php',
        'app/zoosper-global-announcements/src/Announcement/AdminAnnouncementRepository.php',
        'app/zoosper-core/src/Cache/CacheKeyBuilder.php',
        'app/zoosper-core/src/Filesystem/ProjectPathResolver.php',
        'app/zoosper-core/src/Event/EventDispatcherInterface.php',
        'app/zoosper-core/src/Http/Application.php',
        'app/zoosper-core/src/Site/SiteLookupInterface.php',
        'app/zoosper-core/src/Url/CdnUrlResolver.php',
        'app/zoosper-editor/src/ContentEditorRegistry.php',
        'app/zoosper-editor/src/Config/ContentEditorRuntimeConfig.php',
        'app/zoosper-editor/src/Config/ContentEditorRuntimeConfigFactory.php',
        'app/zoosper-auth/src/Admin/Grid/AuthGridPage.php',
        'app/zoosper-auth/src/Repository/AdminUserRepository.php',
        'app/zoosper-auth/src/Service/SessionGuard.php',
        'app/zoosper-page/src/Repository/PageRevisionRepository.php',
        'app/zoosper-page/src/Service/PageRevisionService.php',
        'app/zoosper-two-factor/src/Challenge/TwoFactorChallengeRepository.php',
        'packages/zoosper-admin-grid/src/GridPreferenceRepository.php',
        'packages/zoosper-audit/src/Admin/Grid/OperationalGridPage.php',
        'packages/zoosper-audit/src/AuditLogRepository.php',
    ];

    $runtimeFiles = [];
    foreach (['app', 'packages', 'config'] as $directory) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $path = str_replace($root . '/', '', $file->getPathname());
            if (str_contains($path, '/tests/')) {
                continue;
            }
            $runtimeFiles[$path] = (string) file_get_contents($file->getPathname());
        }
    }

    foreach ($sources as $source) {
        $class = basename($source, '.php');
        $sourceText = (string) file_get_contents($root . '/' . $source);
        $consumers = array_filter(
            $runtimeFiles,
            static fn (string $text, string $path): bool => $path !== $source && str_contains($text, $class),
            ARRAY_FILTER_USE_BOTH,
        );

        expect($sourceText)->toContain('@psalm-api')
            ->and($consumers)->not->toBeEmpty("No runtime registration or direct consumer found for {$source}");
    }
});
