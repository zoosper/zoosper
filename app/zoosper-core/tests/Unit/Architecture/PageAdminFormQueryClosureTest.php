<?php

declare(strict_types=1);

it('moves Page form presentation out of the controller and removes Page runtime query globals', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $factory = (string) file_get_contents($root . '/app/zoosper-page/config/controllers.php');
    $pageSource = '';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/app/zoosper-page/src', FilesystemIterator::SKIP_DOTS,
    ));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $pageSource .= (string) file_get_contents($file->getPathname());
        }
    }
    $gridResponder = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageAdminGridResponder.php'
    );
    $csvController = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/Controller/PageCsvExportController.php'
    );
    expect($controller)->toContain('PageAdminFormRenderer')
        ->not->toContain('$request->queryParams()')
        ->and($gridResponder)->toContain('$request->queryParams()')
        ->and($csvController)->toContain('$request->queryParams()')
        ->and($controller)
        ->not->toContain('private function form(')
        ->not->toContain('private function renderContentEditor(')
        ->not->toContain('private function siteOptions(')
        ->not->toContain('private function defaultPageFormSectionRegistry(')
        ->not->toContain('use Zoosper\\Admin\\Form\\AdminFormConfigAggregator;')
        ->not->toContain('new AdminFormConfigAggregator(')
        ->and($pageSource)->not->toContain('$_GET')
        ->and($factory)->toContain('formRenderer: new PageAdminFormRenderer(');
});
