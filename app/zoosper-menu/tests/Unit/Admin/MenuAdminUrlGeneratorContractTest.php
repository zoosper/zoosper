<?php

declare(strict_types=1);

use Zoosper\Core\Url\AdminUrlGenerator;

it('uses the installed AdminUrlGenerator public path method', function (): void {
    expect(method_exists(AdminUrlGenerator::class, 'url'))->toBeTrue();

    $root = dirname(__DIR__, 3);
    foreach ([
        $root . '/src/Admin/MenuAdminResponder.php',
        $root . '/src/Admin/Controller/MenuAdminController.php',
    ] as $file) {
        $source = (string) file_get_contents($file);
        expect($source)
            ->toContain('->url(')
            ->not->toContain('->to(');
    }
});
