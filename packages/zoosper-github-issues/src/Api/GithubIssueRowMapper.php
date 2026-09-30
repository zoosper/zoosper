<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues\Api;
use DateTimeImmutable;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMappingException;
use Zoosper\ApiGrid\Mapping\ApiGridRowMapperInterface;
final class GithubIssueRowMapper implements ApiGridRowMapperInterface
{
    public function map(array $record): array
    {
        $number = $record['number'] ?? null;
        $title = $record['title'] ?? null;
        $state = $record['state'] ?? null;
        $updatedAt = $record['updated_at'] ?? null;
        if (!is_int($number) || $number < 1 || !is_string($title) || trim($title) === ''
            || !is_string($state) || !is_string($updatedAt)) {
            throw new ApiGridResponseMappingException('GitHub issue response does not match the required schema.');
        }
        try {
            $updated = new DateTimeImmutable($updatedAt);
        } catch (\Throwable $exception) {
            throw new ApiGridResponseMappingException('GitHub issue timestamp is invalid.', previous: $exception);
        }
        $labels = $record['labels'] ?? [];
        if (!is_array($labels)) {
            throw new ApiGridResponseMappingException('GitHub issue labels do not match the required schema.');
        }
        $names = [];
        foreach ($labels as $label) {
            if (!is_array($label) || !is_string($label['name'] ?? null)) {
                throw new ApiGridResponseMappingException('GitHub issue label does not match the required schema.');
            }
            $name = trim($label['name']);
            if ($name !== '') $names[] = $name;
        }
        return [
            'number' => $number,
            'title' => trim($title),
            'kind' => array_key_exists('pull_request', $record) ? 'Pull request' : 'Issue',
            'state' => $state,
            'labels' => implode(', ', array_slice(array_values(array_unique($names)), 0, 10)),
            'updated_at' => $updated->format(DATE_ATOM),
        ];
    }
}
