<?php

declare(strict_types=1);

namespace Zoosper\Api\Controller;

use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Declarative runtime entry point discovered through module configuration.
 *
 * @psalm-api
 */
final readonly class HelloController
{
    public function __construct(private JsonResponder $json)
    {
    }

    public function show(Request $request): Response
    {
        return $this->json->success([
            'message' => 'Hello from Zoosper API.',
            'phase' => '0.3-site-page-rendering',
        ]);
    }
}










