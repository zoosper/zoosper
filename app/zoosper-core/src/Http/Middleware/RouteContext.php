<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

/**
 * Immutable route metadata passed to middleware.
 *
 * Phase 1.33b: `permission` accepts a string, a list of strings, or null and is
 * normalised to a list with OR semantics - a route may require ANY ONE of the
 * declared permissions (e.g. role.manage OR user.manage).
 */
final readonly class RouteContext
{
    /** @var list<string> */
    public array $permissions;

    /** @param string|list<string>|null $permission */
    public function __construct(
        public string $method,
        public string $path,
        public bool $isPublic = false,
        string|array|null $permission = null,
    ) {
        $this->permissions = self::normalise($permission);
    }

    /** @return list<string> */
    public function requiresAnyPermission(): array
    {
        return $this->permissions;
    }

    /**
     * @param string|list<string>|null $permission
     * @return list<string>
     */
    private static function normalise(string|array|null $permission): array
    {
        if ($permission === null) {
            return [];
        }

        if (is_string($permission)) {
            return $permission === '' ? [] : [$permission];
        }

        $result = [];
        foreach ($permission as $value) {
            if (is_string($value) && $value !== '') {
                $result[] = $value;
            }
        }

        return array_values($result);
    }
}