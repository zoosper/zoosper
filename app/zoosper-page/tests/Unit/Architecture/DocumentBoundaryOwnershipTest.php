<?php

declare(strict_types=1);

it('keeps the document boundary Page-owned and out of Page HTTP controllers', function (): void {
    $root = dirname(__DIR__, 3);

    foreach ([
        'src/Api/PageApiController.php',
        'src/Api/ContentPageController.php',
        'src/Admin/Controller/PageAdminController.php',
    ] as $file) {
        $source = (string) file_get_contents($root . '/' . $file);

        expect($source)
            ->not->toContain('new DocumentNormalizer(')
            ->not->toContain('new DocumentRenderer(')
            ->not->toContain('json_decode(')
            ->not->toContain('BlockJsonToHtmlRenderer');
    }

    expect($root . '/src/Content/DocumentValidator.php')->toBeFile()
        ->and($root . '/src/Content/DocumentNormalizer.php')->toBeFile()
        ->and($root . '/src/Content/DocumentRenderer.php')->toBeFile();
});
