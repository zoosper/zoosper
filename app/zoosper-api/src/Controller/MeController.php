<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class MeController
{
    public function __construct(
        private JsonResponder $json,
        private SessionGuard $guard,
    ) {
    }

    public function show(Request $request): Response
    {
        $user = $this->guard->user();

        if ($user === null) {
            return $this->json->error('unauthenticated', 'You are not logged in.', 401);
        }

        return $this->json->success([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'permissions' => $user->permissions,
            ],
        ]);
    }
}
