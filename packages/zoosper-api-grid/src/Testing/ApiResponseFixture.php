<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Testing;
use Zoosper\ApiGrid\Transport\ApiResponse;
/** @psalm-api */
final class ApiResponseFixture
{
    /** @param array<string,mixed>|list<mixed> $body @param array<string,string> $headers */
    public static function success(array $body, array $headers = [], ?int $receivedBodyBytes = null): ApiResponse
    {
        return new ApiResponse(200, $body, $receivedBodyBytes ?? strlen(json_encode($body, JSON_THROW_ON_ERROR)), $headers);
    }
    /** @param array<string,string> $headers */
    public static function failure(int $status, array $headers = []): ApiResponse
    {
        if ($status >= 200 && $status < 300) throw new \InvalidArgumentException('Failure fixture requires a non-success status.');
        return new ApiResponse($status, [], 0, $headers);
    }
}
