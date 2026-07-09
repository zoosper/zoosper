<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class HealthController
{
    public function __construct(private JsonResponder $json)
    {
    }

    public function show(Request $request): Response
    {
        return $this->json->success([
            'service' => 'zoosper',
            'status' => 'ok',
            'version' => '0.1.0-dev',
        ]);
    }
}
