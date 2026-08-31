<?php

declare(strict_types=1);

it('avoids deprecated GD destruction and warning-prone missing-file unlink calls', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Processing/GdMediaProcessor.php');
    expect($source)
        ->not->toContain('imagedestroy(')
        ->not->toContain('@unlink(')
        ->toContain('unlinkIfPresent($temporary)')
        ->toContain('$writtenFiles[] = [$target->absolutePath, $publicAbsolute]')
        ->toContain('$this->unlinkIfPresent($privatePath)')
        ->toContain('$this->unlinkIfPresent($publicPath)');
});











