<?php

declare(strict_types=1);

it('ships a reusable executable-JavaScript extractor', function (): void {
    $root=dirname(__DIR__,5);$support=file_get_contents($root.'/app/zoosper-settings/tests/Support/extract-settings-javascript.php');
    expect($support)->toContain("application/json")
        ->toContain("Executable JavaScript contains unresolved PHP")
        ->toContain("No executable JavaScript blocks found")
        ->toContain('file_put_contents($argv[2]');
});
