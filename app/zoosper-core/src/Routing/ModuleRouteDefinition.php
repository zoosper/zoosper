<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

final readonly class ModuleRouteDefinition
{
    /** @param list<string> $permissions */
    public function __construct(
        public string $method,
        public string $path,
        public string $controller,
        public string $action,
        public array $permissions = [],
        public bool $public = false,
        public bool $stateless = false,
    ) {
    }

    /**
     * Require an Admin route to declare exactly one access classification.
     *
     * Public routes are intentionally unauthenticated entry points. Every other
     * Admin route must declare one or more permissions. Declaring both is
     * contradictory and omitting both would make access depend on an implicit
     * middleware default.
     */
    public function assertExplicitAdminAccess(): void
    {
        $permissionProtected = $this->permissions !== [];
        if ($this->public === $permissionProtected) {
            throw new \Zoosper\Errors\ZoosperException(
                message: 'Admin route must be exactly public or permission protected: ' . $this->method . ' ' . $this->path,
                context: 'The Admin route access classification is missing or contradictory.',
                suggestion: 'Set public=true only for an intentionally unauthenticated Admin entry point; otherwise declare a non-empty permission value.',
                docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                details: [
                    'method' => $this->method,
                    'path' => $this->path,
                    'public' => $this->public,
                    'permissions' => $this->permissions,
                ],
            );
        }
    }

    /**
     * Normalise a raw route `permission` value (string, list of strings, or null)
     * into a list of permission strings with OR semantics.
     *
     * @return list<string>
     */
    public static function normalisePermissions(mixed $permission): array
    {
        if ($permission === null) {
            return [];
        }

        if (is_string($permission)) {
            return $permission === '' ? [] : [$permission];
        }

        if (is_array($permission)) {
            $result = [];
            foreach ($permission as $value) {
                if (is_string($value) && $value !== '') {
                    $result[] = $value;
                }
            }

            return array_values($result);
        }

        return [];
    }
}










