<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Testing;
use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\ApiGrid\Transport\ApiTransportInterface;
/** @psalm-api */
final class FakeApiTransport implements ApiTransportInterface
{
    /** @var list<ApiRequest> */
    private array $requests = [];
    /** @param list<ApiResponse> $responses */
    public function __construct(private array $responses) {}
    #[\Override]
    public function send(ApiRequest $request, ApiReliabilityPolicy $policy): ApiResponse
    {
        $this->requests[] = $request;
        if ($this->responses === []) {
            throw new \OutOfBoundsException('Fake API transport has no queued response.');
        }
        return array_shift($this->responses);
    }
    /** @return list<ApiRequest> */
    public function requests(): array { return $this->requests; }
    public function lastRequest(): ApiRequest
    {
        if ($this->requests === []) throw new \OutOfBoundsException('Fake API transport has not received a request.');
        return $this->requests[array_key_last($this->requests)];
    }
}
