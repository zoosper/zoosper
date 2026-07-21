<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

test('upload derivative seam helper uses constructor parser instead of brittle errorhandler regex', function () {
    $root = dirname(__DIR__, 3);
    $tool = (string) file_get_contents($root . '/tools/apply-upload-derivative-processing-seam.php');

    expect($tool)->toContain('function addConstructorDependency');
    expect($tool)->toContain('public function __construct(');
    expect($tool)->toContain('private ?MediaUploadDerivativeDispatcher $derivatives = null');
    expect($tool)->toContain('Could not safely add MediaUploadDerivativeDispatcher constructor dependency');
    expect($tool)->not->toContain('/private \\?ErrorHandler \\$errorHandler = null,');
});
