<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin;

final class PersonalAccessTokenScopePresenter
{
    /**
     * @param list<string> $scopes
     * @return list<array{code: string, label: string, scopes: list<array{code: string, kind: string}>}>
     */
    public function groups(array $scopes): array
    {
        $labels = [
            'pages' => 'Pages',
            'media' => 'Media',
            'menus' => 'Menus',
            'url_rewrites' => 'URL rewrites',
            'sites' => 'Sites',
            'themes' => 'Themes',
        ];
        $groups = [];

        foreach ($scopes as $scope) {
            [$code, $action] = array_pad(explode(':', $scope, 2), 2, '');
            if (!isset($groups[$code])) {
                $groups[$code] = [
                    'code' => $code,
                    'label' => $labels[$code] ?? ucfirst(str_replace('_', ' ', $code)),
                    'scopes' => [],
                ];
            }

            $groups[$code]['scopes'][] = [
                'code' => $scope,
                'kind' => $action === 'delete' ? 'destructive' : ($action === 'read' ? 'read' : 'write'),
            ];
        }

        return array_values($groups);
    }
}
