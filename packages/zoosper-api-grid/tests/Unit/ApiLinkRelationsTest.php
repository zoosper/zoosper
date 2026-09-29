<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Tests\Unit;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMappingException;
use Zoosper\ApiGrid\Mapping\ApiLinkRelations;
it('extracts opaque next and previous cursors without exposing URLs', function (): void {
    $relations = (new ApiLinkRelations())->parse(
        '<https://api.example.test/items?page=3&after=next-token>; rel="next", '
        . '<https://api.example.test/items?page=1&before=prev-token>; rel="prev"',
    );
    expect($relations)->toBe(['next' => 'next-token', 'prev' => 'prev-token']);
});
it('accepts absent Link metadata as a terminal collection page', function (): void {
    expect((new ApiLinkRelations())->parse(null))->toBe([])
        ->and((new ApiLinkRelations())->parse(''))->toBe([]);
});
it('rejects malformed duplicate unsafe and oversized Link metadata', function (string $header): void {
    expect(fn (): array => (new ApiLinkRelations())->parse($header))
        ->toThrow(ApiGridResponseMappingException::class);
})->with([
    'malformed' => 'not-a-link',
    'duplicate next' => '<https://api.example.test/a?after=one>; rel="next", <https://api.example.test/b?after=two>; rel="next"',
    'credentials' => '<https://user:secret@api.example.test/a?after=one>; rel="next"',
    'http' => '<http://api.example.test/a?after=one>; rel="next"',
    'missing cursor' => '<https://api.example.test/a?page=2>; rel="next"',
    'line injection' => "<https://api.example.test/a?after=one>; rel=\"next\"\r\nX-Evil: yes",
    'oversized' => str_repeat('a', 8193),
]);
it('ignores unrelated well-formed relations', function (): void {
    expect((new ApiLinkRelations())->parse(
        '<https://api.example.test/items?page=1>; rel="first"',
    ))->toBe([]);
});
