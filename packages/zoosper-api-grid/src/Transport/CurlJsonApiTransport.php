<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

use JsonException;

/** Bounded read-only JSON transport for API-backed Grids. */
final readonly class CurlJsonApiTransport implements ApiTransportInterface
{
    public function __construct(private string $baseUrl)
    {
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('API Grid base URL must be an absolute URL.');
        }
    }

    public function send(ApiRequest $request, ApiReliabilityPolicy $policy): ApiResponse
    {
        $url = rtrim($this->baseUrl, '/') . $request->endpoint;
        if ($request->query !== []) {
            $url .= '?' . http_build_query($request->query, '', '&', PHP_QUERY_RFC3986);
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new ApiTransportException('Unable to initialise the external Grid transport.');
        }

        $headers = ['Accept: application/json'];
        foreach ($request->headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT_MS => $policy->connectTimeoutMilliseconds,
            CURLOPT_TIMEOUT_MS => $policy->requestTimeoutMilliseconds,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => 0,
        ]);

        $body = curl_exec($handle);
            if (!is_string($body)) {
                throw new ApiTransportException('External Grid request failed: ' . curl_error($handle));
            }
            if (strlen($body) > $policy->maximumResponseBytes) {
                throw new ApiTransportException('External Grid response exceeded the configured size limit.');
            }
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            try {
                $decoded = $body === '' ? [] : json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new ApiTransportException('External Grid response was not valid JSON.', previous: $exception);
            }
            if (!is_array($decoded)) {
                throw new ApiTransportException('External Grid JSON root must be an object or array.');
            }

        return new ApiResponse($status, $decoded);
    }
}
