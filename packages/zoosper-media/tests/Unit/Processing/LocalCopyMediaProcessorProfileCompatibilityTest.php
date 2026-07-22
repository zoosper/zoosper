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

test('local copy processor falls back to resolved target public path when writer returns void', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Processing/LocalCopyMediaProcessor.php');

    expect($source)->toContain('private function publicDerivativePath');
    expect($source)->toContain('return $target->publicPath;');
    expect($source)->toContain('$derivatives[$profileName] = $this->publicDerivativePath($target, $written);');
});
