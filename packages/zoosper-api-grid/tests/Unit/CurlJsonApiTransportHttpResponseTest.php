<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\ApiGrid\Transport\CurlJsonApiTransport;

it('handles HEAD and retains only final response metadata', function (string $method, string $wireResponse, array $headers): void {
    if (!function_exists('pcntl_fork')) {
        test()->markTestSkipped('The loopback fixture requires pcntl.');
    }

    $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    expect($server)->not->toBeFalse();
    $address = stream_socket_get_name($server, false);
    expect($address)->not->toBeFalse();

    $pid = pcntl_fork();
    if ($pid === 0) {
        $client = stream_socket_accept($server, 5);
        if ($client !== false) {
            $requestLine = fgets($client);
            while (($line = fgets($client)) !== false && trim($line) !== '') {
            }
            fwrite($client, $wireResponse);
            fclose($client);
        }
        fclose($server);
        exit($client !== false && str_starts_with($requestLine, $method . ' ') ? 0 : 1);
    }
    expect($pid)->toBeGreaterThan(0);
    fclose($server);

    try {
        $response = (new CurlJsonApiTransport('http://' . $address))->send(
            new ApiRequest($method, '/'),
            new ApiReliabilityPolicy(requestTimeoutMilliseconds: 1000),
        );
        expect($response->statusCode)->toBe(200)
            ->and($response->decodedBody)->toBe([])
            ->and($response->headers)->toBe($headers);
    } finally {
        pcntl_waitpid($pid, $status);
        expect(pcntl_wexitstatus($status))->toBe(0);
    }
})->with([
    'HEAD with representation length' => [
        'HEAD', "HTTP/1.1 200 OK\r\nContent-Length: 100\r\nConnection: close\r\n\r\n", [],
    ],
    'interim pagination metadata' => [
        'GET', "HTTP/1.1 103 Early Hints\r\nLink: <https://example.test/?after=old>; rel=\"next\"\r\nRetry-After: 10\r\n\r\n"
            . "HTTP/1.1 200 OK\r\nLink: <https://example.test/?after=new>; rel=\"next\"\r\nContent-Length: 2\r\nConnection: close\r\n\r\n[]",
        ['link' => '<https://example.test/?after=new>; rel="next"'],
    ],
    'interim-only link is not a next page' => [
        'GET', "HTTP/1.1 103 Early Hints\r\nLink: <https://example.test/?after=old>; rel=\"next\"\r\n\r\n"
            . "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: close\r\n\r\n[]",
        [],
    ],
    'repeated final links are retained' => [
        'GET', "HTTP/1.1 200 OK\r\nLink: <https://example.test/?after=new>; rel=\"next\"\r\n"
            . "Link: <https://example.test/?before=old>; rel=\"prev\"\r\nContent-Length: 2\r\nConnection: close\r\n\r\n[]",
        ['link' => '<https://example.test/?after=new>; rel="next", <https://example.test/?before=old>; rel="prev"'],
    ],
]);