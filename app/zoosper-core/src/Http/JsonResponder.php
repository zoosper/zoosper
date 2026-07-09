<?php
declare(strict_types=1);

namespace Zoosper\Core\Http;
final readonly class JsonResponder
{
    public function success(array $data = [], int $statusCode = 200): Response
    {
        return Response::json(['success' => true, 'data' => $data], $statusCode);
    }

    public function error(string $code, string $message, int $statusCode = 400, array $meta = []): Response
    {
        return Response::json(['success' => false, 'error' => ['code' => $code, 'message' => $message, 'meta' => $meta]], $statusCode);
    }
}
