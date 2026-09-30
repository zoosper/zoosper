<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues\Api;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridRequestMapperInterface;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\Grid\DataSource\GridQuery;
final class GithubIssueRequestMapper implements ApiGridRequestMapperInterface
{
    public function map(GridQuery $query, ApiGridContext $context): ApiRequest
    {
        $owner = $this->segment($context->scope['owner'] ?? null, 'owner');
        $repository = $this->segment($context->scope['repository'] ?? null, 'repository');
        $parameters = ['state' => 'open', 'per_page' => $query->pageSize];
        if ($query->cursor !== null) {
            [$direction, $token] = array_pad(explode(':', $query->cursor, 2), 2, '');
            if (!in_array($direction, ['after', 'before'], true) || $token === '') {
                throw new \InvalidArgumentException('GitHub Issues cursor is invalid.');
            }
            $parameters[$direction] = $token;
        }
        return new ApiRequest('GET', "/repos/{$owner}/{$repository}/issues", $parameters, [
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent' => 'Zoosper-API-Grid',
        ]);
    }
    private function segment(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[A-Za-z0-9_.-]{1,100}$/', $value) !== 1) {
            throw new \InvalidArgumentException("GitHub {$label} is invalid.");
        }
        return $value;
    }
}
