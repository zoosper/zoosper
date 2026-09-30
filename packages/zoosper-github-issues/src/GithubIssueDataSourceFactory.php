<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues;
use Zoosper\ApiGrid\ApiGridDataSource;
use Zoosper\ApiGrid\Authentication\NoAuthentication;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\CurlJsonApiTransport;
use Zoosper\GithubIssues\Api\GithubIssueRequestMapper;
use Zoosper\GithubIssues\Api\GithubIssueResponseMapper;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridPaginationMode;
final class GithubIssueDataSourceFactory
{
    public function create(string $owner, string $repository, int $adminUserId): ApiGridDataSource
    {
        return new ApiGridDataSource(
            new CurlJsonApiTransport('https://api.github.com'),
            new GithubIssueRequestMapper(),
            new GithubIssueResponseMapper(),
            new NoAuthentication(),
            new ApiGridContext($adminUserId, scope: ['owner' => $owner, 'repository' => $repository]),
            new GridDataSourceCapabilities(paginationMode: GridPaginationMode::Cursor),
            new ApiReliabilityPolicy(maximumResponseBytes: 500000),
        );
    }
}
