<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues\Tests\Unit;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMappingException;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\GithubIssues\Api\GithubIssueRequestMapper;
use Zoosper\GithubIssues\Api\GithubIssueResponseMapper;
use Zoosper\Grid\DataSource\GridPaginationMode;
use Zoosper\Grid\DataSource\GridQuery;
it('maps deployment-owned repository scope and an opaque cursor', function (): void {
    $request = (new GithubIssueRequestMapper())->map(
        new GridQuery(pageSize: 20, cursor: 'after:opaque-token'),
        new ApiGridContext(7, scope: ['owner' => 'github', 'repository' => 'docs']),
    );
    expect($request->endpoint)->toBe('/repos/github/docs/issues')
        ->and($request->query)->toBe(['state' => 'open', 'per_page' => 20, 'after' => 'opaque-token'])
        ->and($request->headers)->toHaveKeys(['Accept', 'X-GitHub-Api-Version', 'User-Agent']);
});
it('maps a list envelope and distinguishes issues from pull requests', function (): void {
    $response = new ApiResponse(200, [
        ['number' => 10, 'title' => 'Issue title', 'state' => 'open', 'updated_at' => '2026-09-30T00:00:00Z', 'labels' => [['name' => 'docs']], 'body' => 'private body', 'html_url' => 'https://example.test/10'],
        ['number' => 11, 'title' => 'PR title', 'state' => 'open', 'updated_at' => '2026-09-30T01:00:00Z', 'labels' => [], 'pull_request' => ['url' => 'https://example.test/pr/11']],
    ], 600, ['link' => '<https://api.github.com/repositories/1/issues?after=next-token>; rel="next"']);
    $result = (new GithubIssueResponseMapper())->map($response, new GridQuery(pageSize: 20));
    expect($result->paginationMode)->toBe(GridPaginationMode::Cursor)
        ->and($result->nextCursor)->toBe('after:next-token')
        ->and($result->previousCursor)->toBeNull()
        ->and($result->items[0])->toBe(['number' => 10, 'title' => 'Issue title', 'kind' => 'Issue', 'state' => 'open', 'labels' => 'docs', 'updated_at' => '2026-09-30T00:00:00+00:00'])
        ->and($result->items[1]['kind'])->toBe('Pull request')
        ->and($result->items[0])->not->toHaveKeys(['body', 'html_url']);
});
it('rejects schema drift without retaining payload values', function (): void {
    try {
        (new GithubIssueResponseMapper())->map(new ApiResponse(200, [['number' => 1, 'title' => 'secret-payload']], 30), new GridQuery());
        test()->fail('Expected schema drift.');
    } catch (ApiGridResponseMappingException $exception) {
        expect($exception->getMessage())->not->toContain('secret-payload');
    }
});
it('rejects untrusted repository path segments', function (): void {
    expect(fn () => (new GithubIssueRequestMapper())->map(
        new GridQuery(), new ApiGridContext(1, scope: ['owner' => '../bad', 'repository' => 'docs']),
    ))->toThrow(\InvalidArgumentException::class);
});
