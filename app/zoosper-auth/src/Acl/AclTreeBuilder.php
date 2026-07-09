<?php

declare(strict_types=1);

namespace Zoosper\Auth\Acl;

final readonly class AclTreeBuilder
{
    /**
     * @param list<array<string, mixed>> $permissions
     * @param list<array<string, mixed>> $groups
     * @return list<AclGroup>
     */
    public function build(array $permissions, array $groups): array
    {
        $groupMap = [];
        foreach ($groups as $group) {
            $code = (string) ($group['code'] ?? 'other');
            $groupMap[$code] = [
                'code' => $code,
                'label' => (string) ($group['label'] ?? ucfirst($code)),
                'sort_order' => (int) ($group['sort_order'] ?? 100),
                'permissions' => [],
            ];
        }

        foreach ($permissions as $permission) {
            $parentCode = (string) ($permission['parent_code'] ?? '');
            if ($parentCode === '') {
                $parentCode = $this->prefixGroup((string) $permission['code']);
            }
            if (!isset($groupMap[$parentCode])) {
                $groupMap[$parentCode] = [
                    'code' => $parentCode,
                    'label' => ucfirst(str_replace(['_', '-'], ' ', $parentCode)),
                    'sort_order' => 100,
                    'permissions' => [],
                ];
            }
            $groupMap[$parentCode]['permissions'][] = $permission;
        }

        $groups = array_values(array_filter($groupMap, static fn (array $group): bool => $group['permissions'] !== []));
        usort($groups, static fn (array $a, array $b): int => [$a['sort_order'], $a['label']] <=> [$b['sort_order'], $b['label']]);

        return array_map(static function (array $group): AclGroup {
            usort($group['permissions'], static fn (array $a, array $b): int => [(int) ($a['sort_order'] ?? 100), (string) $a['code']] <=> [(int) ($b['sort_order'] ?? 100), (string) $b['code']]);
            return new AclGroup(
                code: (string) $group['code'],
                label: (string) $group['label'],
                sortOrder: (int) $group['sort_order'],
                permissions: $group['permissions'],
            );
        }, $groups);
    }

    private function prefixGroup(string $permissionCode): string
    {
        $prefix = explode('.', $permissionCode)[0] ?? 'other';
        return $prefix !== '' ? $prefix : 'other';
    }
}
