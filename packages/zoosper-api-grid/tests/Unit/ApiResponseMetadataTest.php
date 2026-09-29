<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Tests\Unit;
use Zoosper\ApiGrid\Transport\ApiResponse;
it('carries only explicitly supplied immutable response metadata', function (): void {
    $response = new ApiResponse(
        200,
        [['number' => 1]],
        32,
        ['link' => '<https://api.example.test/items?page=2>; rel="next"'],
    );
    expect($response->headers)->toBe([
        'link' => '<https://api.example.test/items?page=2>; rel="next"',
    ])->and($response->receivedBodyBytes)->toBe(32);
});
