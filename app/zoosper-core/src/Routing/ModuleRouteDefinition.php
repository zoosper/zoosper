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
    ) {
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
