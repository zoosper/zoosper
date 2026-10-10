<?php

declare(strict_types=1);

it('keeps Page Admin form collaborators fail closed and successful create IDs non-null', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $coordinator = (string) file_get_contents($root . '/app/zoosper-page/src/Application/Save/PageSaveCoordinator.php');

    expect($controller)
        ->toContain("throw new RuntimeException('Page Admin form services are unavailable.')")
        ->toContain("throw new RuntimeException('Successful Page creation did not return a Page ID.')")
        ->toContain('private function html(string $title, string $content, int $status = 200): Response')
        ->toContain("?? \$this->layout->render(\$title, \$content, \$user, 'pages')")
        ->and($coordinator)
        ->toContain("throw new RuntimeException('Page persistence completed without a Page ID.')")
        ->toContain('return PageSaveResult::success($pageId);');
});