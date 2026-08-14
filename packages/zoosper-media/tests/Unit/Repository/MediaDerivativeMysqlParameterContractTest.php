<?php

declare(strict_types=1);

it('uses one distinct bound parameter for every derivative insert placeholder', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Repository/MediaDerivativeRepository.php');

    expect($source)
        ->toContain(':created_at, :updated_at')
        ->toContain("'created_at'=>\$now")
        ->toContain("'updated_at'=>\$now")
        ->not->toContain(':now, :now');
});
