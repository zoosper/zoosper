<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Mapping;
/**
 * Parses bounded Link metadata into opaque relation tokens without following remote URLs.
 *
 * @psalm-api
 */
final class ApiLinkRelations
{
    private const MAXIMUM_HEADER_BYTES = 8192;
    /** @return array<string, string> */
    public function parse(?string $header): array
    {
        if ($header === null || trim($header) === '') {
            return [];
        }
        if (strlen($header) > self::MAXIMUM_HEADER_BYTES || str_contains($header, "\r") || str_contains($header, "\n")) {
            throw new ApiGridResponseMappingException('External Grid Link metadata is invalid.');
        }
        $relations = [];
        foreach (array_map('trim', explode(',', $header)) as $value) {
            if (preg_match('/^<([^<>]+)>\s*;\s*rel="([a-z][a-z0-9_-]*)"$/i', $value, $match) !== 1) {
                throw new ApiGridResponseMappingException('External Grid Link metadata is invalid.');
            }
            $url = $match[1];
            $relation = strtolower($match[2]);
            if (!in_array($relation, ['next', 'prev'], true)) {
                continue;
            }
            if (isset($relations[$relation])) {
                throw new ApiGridResponseMappingException('External Grid Link metadata contains duplicate relations.');
            }
            $parts = parse_url($url);
            if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])
                || strtolower($parts['scheme']) !== 'https'
                || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
                throw new ApiGridResponseMappingException('External Grid Link relation URL is invalid.');
            }
            parse_str($parts['query'] ?? '', $query);
            $token = $query['after'] ?? $query['before'] ?? null;
            if (!is_string($token) || $token === '' || strlen($token) > 2048
                || preg_match('/^[A-Za-z0-9._~%=-]+$/', $token) !== 1) {
                throw new ApiGridResponseMappingException('External Grid Link cursor is invalid.');
            }
            $relations[$relation] = $token;
        }
        return $relations;
    }
}
