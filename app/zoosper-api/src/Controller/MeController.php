<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Auth\Access\RoleProviderInterface;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class MeController
{
    public function __construct(
        private JsonResponder $json,
        private RoleProviderInterface $roles,
    ) {
    }

    public function show(Request $request): Response
    {
        $role = $this->roles->get('super_admin');

        return $this->json->success([
            'user' => [
                'id' => 'placeholder-admin',
                'name' => 'Zoosper Admin',
                'role' => $role?->code,
                'permissions' => array_map(
                    static fn ($permission): string => $permission->value,
                    $role?->permissions ?? [],
                ),
            ],
        ]);
    }
}
