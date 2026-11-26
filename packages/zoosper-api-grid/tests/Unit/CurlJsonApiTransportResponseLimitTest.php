<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\ApiGrid\Transport\ApiTransportException;
use Zoosper\ApiGrid\Transport\CurlJsonApiTransport;

it('enforces the response size limit without misclassifying normal responses', function (int $limit, bool $tooLarge): void {
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
            while (($line = fgets($client)) !== false && trim($line) !== '') {
            }
            fwrite($client, "HTTP/1.1 200 OK\r\nContent-Length: 7\r\nConnection: close\r\n\r\n{\"a\":1}");
            fclose($client);
        }
        fclose($server);
        exit($client === false ? 1 : 0);
    }
    expect($pid)->toBeGreaterThan(0);
    fclose($server);

    try {
        $transport = new CurlJsonApiTransport('http://' . $address);
        try {
            $response = $transport->send(new ApiRequest('GET', '/'), new ApiReliabilityPolicy(maximumResponseBytes: $limit));
            expect($tooLarge)->toBeFalse();
            expect($response->decodedBody)->toBe(['a' => 1]);
        } catch (ApiTransportException $exception) {
            expect($tooLarge)->toBeTrue();
            expect($exception->category)->toBe(ApiTransportException::RESPONSE_TOO_LARGE);
        }
    } finally {
        pcntl_waitpid($pid, $status);
        expect(pcntl_wexitstatus($status))->toBe(0);
    }
})->with([[7, false], [6, true]]);