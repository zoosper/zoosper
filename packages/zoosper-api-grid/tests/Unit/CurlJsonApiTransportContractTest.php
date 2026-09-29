<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use InvalidArgumentException;
use Zoosper\ApiGrid\Transport\CurlJsonApiTransport;

it('requires an absolute configured base URL', function (): void {
    expect(fn () => new CurlJsonApiTransport('127.0.0.1:3000'))
        ->toThrow(InvalidArgumentException::class);
});

it('keeps the bounded transport free of redirects writes and deprecated handle cleanup', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-api-grid/src/Transport/CurlJsonApiTransport.php',
    );

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('CURLOPT_FOLLOWLOCATION => false')
        ->and($source)->toContain('maximumResponseBytes')
        ->and($source)->toContain('strlen($body)')
        ->and($source)->toContain('JSON_THROW_ON_ERROR')
        ->and($source)->toContain('CURLPROTO_HTTP | CURLPROTO_HTTPS')
        ->and($source)->not->toContain('curl_close(');
});

it('captures only pagination and retry response metadata without exposing arbitrary headers', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents(
        $root . '/packages/zoosper-api-grid/src/Transport/CurlJsonApiTransport.php',
    );
    expect($source)->not->toBeFalse()
        ->and($source)->toContain('CURLOPT_HEADERFUNCTION')
        ->and($source)->toContain("['link', 'retry-after']")
        ->and($source)->toContain('INVALID_RESPONSE_METADATA')
        ->and($source)->toContain('new ApiResponse($status, $decoded, strlen($body), $responseHeaders)')
        ->and($source)->not->toContain('$responseHeaders["authorization"]')
        ->and($source)->not->toContain('$responseHeaders["set-cookie"]');
});
