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
            throw new ApiTransportException(
                'Unable to initialise the external Grid transport.',
                category: ApiTransportException::INITIALISATION,
            );
        }

        $headers = ['Accept: application/json'];
        foreach ($request->headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        $body = '';
        /** @var array<string, string> $responseHeaders */
        $responseHeaders = [];
        $responseTooLarge = false;
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT_MS => $policy->connectTimeoutMilliseconds,
            CURLOPT_TIMEOUT_MS => $policy->requestTimeoutMilliseconds,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => 0,
            CURLOPT_HEADERFUNCTION => static function (\CurlHandle $handle, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, 'HTTP/')) {
                    return $length;
                }
                $separator = strpos($trimmed, ':');
                if ($separator === false) {
                    throw new ApiTransportException(
                        'External Grid response metadata was invalid.',
                        category: ApiTransportException::INVALID_RESPONSE_METADATA,
                    );
                }
                $name = strtolower(trim(substr($trimmed, 0, $separator)));
                $value = trim(substr($trimmed, $separator + 1));
                if (preg_match('/^[a-z0-9!#$%&\'*+.^_`|~-]+$/', $name) !== 1
                    || str_contains($value, "\r")
                    || str_contains($value, "\n")) {
                    throw new ApiTransportException(
                        'External Grid response metadata was invalid.',
                        category: ApiTransportException::INVALID_RESPONSE_METADATA,
                    );
                }
                if (in_array($name, ['link', 'retry-after'], true)) {
                    $responseHeaders[$name] = isset($responseHeaders[$name])
                        ? $responseHeaders[$name] . ', ' . $value
                        : $value;
                }
                return $length;
            },
            CURLOPT_WRITEFUNCTION => static function (\CurlHandle $handle, string $chunk) use (&$body, &$responseTooLarge, $policy): int {
                if (strlen($body) + strlen($chunk) > $policy->maximumResponseBytes) {
                    $responseTooLarge = true;
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);

        $success = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        if ($success === false) {
            if ($responseTooLarge) {
                throw new ApiTransportException(
                    'External Grid response exceeded the configured size limit.',
                    category: ApiTransportException::RESPONSE_TOO_LARGE,
                );
            }
            $errorNumber = curl_errno($handle);
            $category = $errorNumber === CURLE_OPERATION_TIMEDOUT
                ? ApiTransportException::TIMEOUT
                : ApiTransportException::NETWORK;
            throw new ApiTransportException(
                $category === ApiTransportException::TIMEOUT
                    ? 'External Grid request timed out.'
                    : 'External Grid request failed.',
                category: $category,
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new ApiTransportException(
                'External Grid source returned a non-success response.',
                category: ApiTransportException::NON_SUCCESS,
                statusCode: $status,
            );
        }

        try {
            $decoded = $body === '' ? [] : json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ApiTransportException(
                'External Grid response was not valid JSON.',
                previous: $exception,
                category: ApiTransportException::INVALID_JSON,
            );
        }
        if (!is_array($decoded)) {
            throw new ApiTransportException(
                'External Grid JSON root must be an object or array.',
                category: ApiTransportException::INVALID_JSON_ROOT,
            );
        }

        return new ApiResponse($status, $decoded, strlen($body), $responseHeaders);
    }
}











