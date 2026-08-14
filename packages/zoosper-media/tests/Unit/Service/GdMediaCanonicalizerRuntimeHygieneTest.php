<?php

declare(strict_types=1);

it('does not use the PHP 8.5 deprecated GD destruction function', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Service/GdMediaCanonicalizer.php');
    expect($source)
        ->not->toContain('imagedestroy(')
        ->toContain('unset($image)');
});
