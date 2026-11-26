<?php

declare(strict_types=1);

use Zoosper\Errors\ConsoleExceptionFormatter;
use Zoosper\Errors\ZoosperException;

it('formats diagnostic values while preserving encoding fallbacks and redaction', function (): void {
    $zero = new class implements JsonSerializable {
        public function jsonSerialize(): int
        {
            return 0;
        }
    };
    $exception = new ZoosperException('failure', details: [
        'nested' => ['password' => 'hidden', 'message' => 'hello'],
        'list' => [['token' => 'secret', 'value' => 'kept']],
        'invalid' => ["\xB1"],
        'zero' => $zero,
    ]);

    $output = (new ConsoleExceptionFormatter())->format($exception);
    expect($output)->toContain('"password":"[redacted]"')
        ->toContain('"message":"hello"')
        ->toContain('- list: [{"token":"[redacted]","value":"kept"}]')
        ->toContain('- invalid: [unprintable]')
        ->toContain('- zero: [unprintable]')
        ->not->toContain('hidden')
        ->not->toContain('secret');
});