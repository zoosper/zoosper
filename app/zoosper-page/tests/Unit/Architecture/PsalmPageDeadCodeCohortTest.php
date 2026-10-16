<?php

declare(strict_types=1);

it('keeps the completed Page dead-code cohort absent', function (): void {
    $root = dirname(__DIR__, 3);
    foreach ([
        'src/Admin/PageAdminDashboardFactsGuard.php',
        'src/Admin/PageAdminDashboardStatusPresenter.php',
        'src/Form/PageStoreViewOptionsProvider.php',
        'src/Sanitization/PageContentSanitizationAuditor.php',
        'src/Service/PageStoreViewAssignmentService.php',
        'src/Model/PageSiteAssignment.php',
        'src/Repository/PageSiteAssignmentRepository.php',
        'src/Sanitization/PageContentSanitizationResult.php',
    ] as $file) {
        expect($root . '/' . $file)->not->toBeFile();
    }
    expect((string) file_get_contents($root . '/src/Content/PageContentDocument.php'))
        ->not->toContain('function fromRow(')->not->toContain('function toRowValues(')
        ->toContain('@psalm-suppress PossiblyUnusedProperty HTML is the public compatibility and recovery representation.')
        ->and((string) file_get_contents($root . '/src/Content/ContentFormat.php'))
        ->not->toContain('function fromNullable(');
});
