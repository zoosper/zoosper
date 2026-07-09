<?php
declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class MeController
{
    public function __construct(private JsonResponder $json, private SessionGuard $guard)
    {
    }

    public function show(Request $r): Response
    {
        $u = $this->guard->user();
        if ($u === null) return $this->json->error('unauthenticated', 'You are not logged in.', 401);
        return $this->json->success(['user' => ['id' => $u->id, 'email' => $u->email, 'name' => $u->name, 'permissions' => $u->permissions]]);
    }
}
