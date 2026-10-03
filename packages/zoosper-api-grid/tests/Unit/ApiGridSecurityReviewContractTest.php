<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Tests\Unit;
use InvalidArgumentException;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\ApiGrid\Transport\CurlJsonApiTransport;
it('keeps API Grid requests read only and endpoints local to the configured origin', function (): void {
    expect(fn () => new ApiRequest('POST', '/records'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new ApiRequest('GET', 'https://evil.test/records'))->toThrow(InvalidArgumentException::class);
});
it('requires credential-free HTTPS origins outside explicit loopback development', function (string $url): void {
    expect(fn () => new CurlJsonApiTransport($url))->toThrow(InvalidArgumentException::class);
})->with([
    'remote http' => 'http://api.example.test',
    'embedded user info' => 'https://user:secret@api.example.test',
    'base query' => 'https://api.example.test?token=secret',
    'base fragment' => 'https://api.example.test#secret',
]);
it('retains HTTPS and loopback-only HTTP compatibility', function (): void {
    expect(new CurlJsonApiTransport('https://api.example.test'))->toBeInstanceOf(CurlJsonApiTransport::class)
        ->and(new CurlJsonApiTransport('http://127.0.0.1:3000'))->toBeInstanceOf(CurlJsonApiTransport::class)
        ->and(new CurlJsonApiTransport('http://localhost:3000'))->toBeInstanceOf(CurlJsonApiTransport::class);
});
it('keeps the reviewed transport boundary fail closed and secret safe', function (): void {
    $root = dirname(__DIR__, 4);
    $source = (string) file_get_contents($root . '/packages/zoosper-api-grid/src/Transport/CurlJsonApiTransport.php');
    expect($source)->toContain('CURLOPT_FOLLOWLOCATION => false')
        ->toContain('CURLOPT_REDIR_PROTOCOLS => 0')
        ->toContain('maximumResponseBytes')
        ->toContain("['link', 'retry-after']")
        ->not->toContain('curl_error(')
        ->not->toContain('Authorization"]')
        ->not->toContain('set-cookie');
});
