<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class AuthController
{
    public function __construct(
        private JsonResponder $json,
        private AuthService $auth,
        private SessionGuard $guard,
    ) {
    }

    public function login(Request $request): Response
    {
        $payload = $request->json();
        $user = $this->auth->authenticate(
            (string) ($payload['email'] ?? ''),
            (string) ($payload['password'] ?? ''),
        );

        if ($user === null) {
            return $this->json->error('invalid_credentials', 'Invalid email or password.', 401);
        }

        $this->guard->login($user);

        return $this->json->success([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'permissions' => $user->permissions,
            ],
        ]);
    }

    public function logout(Request $request): Response
    {
        $this->guard->logout();

        return $this->json->success(['message' => 'Logged out.']);
    }
}
