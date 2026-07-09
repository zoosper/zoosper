<?php
declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class HelloController
{
    public function __construct(private JsonResponder $json)
    {
    }

    public function show(Request $r): Response
    {
        return $this->json->success(['message' => 'Hello from Zoosper API.', 'phase' => '0.2-auth-database']);
    }
}
