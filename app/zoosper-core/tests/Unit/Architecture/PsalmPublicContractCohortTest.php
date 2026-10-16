<?php

declare(strict_types=1);

it('marks only the selected cross-module contracts as Psalm public API', function (): void {
    $root = dirname(__DIR__, 5);
    $files = [
        'packages/zoosper-cache/src/Contract/CacheInterface.php',
        'packages/zoosper-logger/src/Contract/LoggerInterface.php',
        'app/zoosper-core/src/Entity/Save/FieldDefinition.php',
        'app/zoosper-page/src/Admin/PageGridResponse.php',
        'packages/zoosper-admin-form/src/AdminFormSection.php',
        'packages/zoosper-api-grid/src/Page/ApiGridPage.php',
        'packages/zoosper-grid/src/DataSource/GridResult.php',
        'packages/zoosper-media/src/Lifecycle/MediaLifecycleResult.php',
        'packages/zoosper-media/src/Processing/MediaProcessingPolicy.php',
    ];
    foreach ($files as $file) {
        expect((string) file_get_contents($root . '/' . $file))->toContain('@psalm-api');
    }
});
