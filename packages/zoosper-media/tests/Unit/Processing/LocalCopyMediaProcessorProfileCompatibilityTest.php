<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

use Zoosper\Media\Processing\LocalCopyMediaProcessor;

test('local copy processor passes profile names to the local derivative path resolver', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Processing/LocalCopyMediaProcessor.php');

    expect(class_exists(LocalCopyMediaProcessor::class))->toBeTrue();
    expect($source)->toContain('$profileName = $this->profileName($profile, $index);');
    expect($source)->toContain('$paths->resolve($storagePath, $profileName)');
    expect($source)->toContain('$derivatives[$profileName]');
    expect($source)->toContain('PROFILE_NAME_ACCESSORS');
    expect($source)->toContain('reflectPropertyValue');
    expect($source)->toContain('return \'profile-\' . (string) $index;');
});

test('local copy processor uses the resolved public path after the void writer succeeds', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Processing/LocalCopyMediaProcessor.php');

    expect($source)
        ->toContain('$writer->write($target, $contents);')
        ->toContain('$derivatives[$profileName] = $target->publicPath;')
        ->not->toContain('$written = $writer->write(')
        ->not->toContain('private function publicDerivativePath');
});











