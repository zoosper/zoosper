<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Auth\Contract\SecondFactorRequirementInterface;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
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
        private SecondFactorRequirementInterface $secondFactor,
        private AdminAuthenticationRateLimiterInterface $rateLimiter,
    ) {
    }

    public function login(Request $request): Response
    {
        $payload = $request->json();
        $email = trim((string) ($payload['email'] ?? ''));
        $decision = $this->rateLimiter->checkPasswordLogin($email, $request->clientIp());
        if (!$decision->allowed) {
            return Response::raw(
                json_encode(['success' => false, 'error' => ['code' => 'too_many_attempts', 'message' => 'Too many sign-in attempts.']], JSON_THROW_ON_ERROR),
                429,
                ['Content-Type' => 'application/json; charset=utf-8', 'Retry-After' => (string) max(1, $decision->retryAfterSeconds), 'Cache-Control' => 'no-store'],
            );
        }
        $user = $this->auth->authenticate($email, (string) ($payload['password'] ?? ''));

        if ($user === null) {
            return $this->json->error('invalid_credentials', 'Invalid email or password.', 401);
        }

        if ($this->secondFactor->requiresSecondFactor($user->id)) {
            $this->guard->logout();
            return $this->json->error('second_factor_required', 'API session login is unavailable for accounts requiring two-factor authentication.', 403);
        }
        $this->rateLimiter->resetPasswordLogin($user->email, $request->clientIp());
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










