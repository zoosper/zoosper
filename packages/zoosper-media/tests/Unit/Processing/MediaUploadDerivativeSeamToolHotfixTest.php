<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

test('upload derivative seam migration helper emits constructor dependency literally', function () {
    $root = dirname(__DIR__, 3);
    $tool = (string) file_get_contents($root . '/tools/apply-upload-derivative-processing-seam.php');

    expect($tool)->toContain('preg_replace_callback');
    expect($tool)->toContain('\'private ?MediaUploadDerivativeDispatcher $derivatives = null\'');
    expect($tool)->toContain('\\$this->derivatives?->processAfterUpload(\\$stored->storagePath)');
    expect($tool)->not->toContain('"private ?ErrorHandler $errorHandler = null');
    expect($tool)->not->toContain('"$1\\n\\n            $this->derivatives');
});
