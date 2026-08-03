<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use Zoosper\ApiGrid\Transport\ApiTransportException;

it('defines stable redaction-safe transport failure categories', function (): void {
    expect(ApiTransportException::TIMEOUT)->toBe('timeout')
        ->and(ApiTransportException::RESPONSE_TOO_LARGE)->toBe('response_too_large')
        ->and(ApiTransportException::INVALID_JSON)->toBe('invalid_json')
        ->and(ApiTransportException::NON_SUCCESS)->toBe('non_success');
});

it('keeps transport failures free of raw curl diagnostics and deprecated cleanup', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents($root . '/packages/zoosper-api-grid/src/Transport/CurlJsonApiTransport.php');

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('CURLOPT_WRITEFUNCTION')
        ->and($source)->toContain('CURLE_OPERATION_TIMEDOUT')
        ->and($source)->not->toContain('curl_error(')
        ->and($source)->not->toContain('curl_close(')
        ->and($source)->not->toContain("'External Grid request failed: ' .");
});

it('checks status before attempting to decode an error body', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents($root . '/packages/zoosper-api-grid/src/Transport/CurlJsonApiTransport.php');

    expect($source)->not->toBeFalse();
    $statusCheck = strpos($source, '$status < 200 || $status >= 300');
    $decode = strpos($source, 'json_decode(');
    expect($statusCheck)->not->toBeFalse()->and($decode)->not->toBeFalse();
    expect($statusCheck)->toBeLessThan($decode);
});

it('retains a non-success boundary for replaceable transports', function (): void {
    $root = dirname(__DIR__, 4);
    $source = file_get_contents($root . '/packages/zoosper-api-grid/src/ApiGridDataSource.php');

    expect($source)->not->toBeFalse()
        ->and($source)->toContain('if (!$response->isSuccessful())')
        ->and($source)->toContain('ApiTransportException::NON_SUCCESS');
});
