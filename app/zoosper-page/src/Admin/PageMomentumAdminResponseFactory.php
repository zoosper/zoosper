<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use ReflectionClass;
use Throwable;
use Zoosper\Core\Http\Response;

/**
 * Creates framework Response objects for read-only Page Momentum HTML.
 *
 * This wrapper keeps the dashboard controller independent from the exact local
 * Response constructor shape while ensuring the live router receives a Response
 * object instead of a raw string.
 */
final class PageMomentumAdminResponseFactory
{
    /**
     * @param array<string, string> $headers
     */
    public function html(string $html, int $status = 200, array $headers = []): Response
    {
        $headers = ['Content-Type' => 'text/html; charset=UTF-8'] + $headers;

        foreach ($this->constructors($html, $status, $headers) as $arguments) {
            try {
                /** @var Response $response */
                $response = new Response(...$arguments);
                return $response;
            } catch (Throwable) {
                // Try the next constructor shape.
            }
        }

        foreach (['html', 'fromHtml', 'content'] as $method) {
            if (method_exists(Response::class, $method)) {
                try {
                    /** @var Response $response */
                    $response = Response::{$method}($html, $status, $headers);
                    return $response;
                } catch (Throwable) {
                    // Try the next factory method.
                }
            }
        }

        $reflection = new ReflectionClass(Response::class);
        throw new \RuntimeException(sprintf(
            'Could not create %s for Page Momentum HTML. Check the Response constructor signature in %s.',
            Response::class,
            (string) $reflection->getFileName(),
        ));
    }

    /**
     * @param array<string, string> $headers
     * @return list<array<int, mixed>>
     */
    private function constructors(string $html, int $status, array $headers): array
    {
        return [
            [$html, $status, $headers],
            [$html, $status],
            [$html],
            [$status, $headers, $html],
            [$status, $html, $headers],
            [$status, $html],
            [$status, $headers],
            [],
        ];
    }
}
