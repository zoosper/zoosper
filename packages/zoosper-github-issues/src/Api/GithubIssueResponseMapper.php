<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues\Api;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMapperInterface;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMappingException;
use Zoosper\ApiGrid\Mapping\ApiLinkRelations;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\Grid\DataSource\GridPaginationMode;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;
final readonly class GithubIssueResponseMapper implements ApiGridResponseMapperInterface
{
    public function __construct(
        private GithubIssueRowMapper $rows = new GithubIssueRowMapper(),
        private ApiLinkRelations $links = new ApiLinkRelations(),
    ) {}
    public function map(ApiResponse $response, GridQuery $query): GridResult
    {
        if (!array_is_list($response->decodedBody)) {
            throw new ApiGridResponseMappingException('GitHub issues response must be a list.');
        }
        $items = [];
        foreach ($response->decodedBody as $record) {
            if (!is_array($record)) {
                throw new ApiGridResponseMappingException('Each GitHub issue must be an object.');
            }
            $items[] = $this->rows->map($record);
        }
        $relations = $this->links->parse($response->headers['link'] ?? null);
        return new GridResult(
            items: $items,
            total: count($items),
            page: 1,
            pageSize: $query->pageSize,
            paginationMode: GridPaginationMode::Cursor,
            nextCursor: isset($relations['next']) ? 'after:' . $relations['next'] : null,
            previousCursor: isset($relations['prev']) ? 'before:' . $relations['prev'] : null,
        );
    }
}
