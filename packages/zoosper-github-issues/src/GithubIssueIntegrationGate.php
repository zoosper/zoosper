<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues;
final class GithubIssueIntegrationGate
{
    /** @return array{enabled: bool, owner: string, repository: string} */
    public static function configuration(): array
    {
        $enabled = filter_var(self::value('GITHUB_ISSUES_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        $owner = trim((string) self::value('GITHUB_ISSUES_OWNER', ''));
        $repository = trim((string) self::value('GITHUB_ISSUES_REPOSITORY', ''));
        if ($enabled) {
            self::assertSegment($owner, 'owner');
            self::assertSegment($repository, 'repository');
        }
        return ['enabled' => $enabled, 'owner' => $owner, 'repository' => $repository];
    }
    public static function enabled(): bool { return self::configuration()['enabled']; }
    private static function assertSegment(string $value, string $label): void
    {
        if (preg_match('/^[A-Za-z0-9_.-]{1,100}$/', $value) !== 1) {
            throw new \InvalidArgumentException("GitHub Issues {$label} is invalid.");
        }
    }
    private static function value(string $key, mixed $default): mixed
    {
        return function_exists('env') ? env($key, $default) : (getenv($key) !== false ? getenv($key) : $default);
    }
    private function __construct() {}
}
