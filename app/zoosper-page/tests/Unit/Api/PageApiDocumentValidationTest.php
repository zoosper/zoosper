<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Api;

use ReflectionClass;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Api\PageApiController;
use Zoosper\Page\Content\DocumentNormalizer;
use Zoosper\Page\Content\DocumentValidator;

it('returns a validation response for malformed API documents', function (array $document): void {
    $reflection = new ReflectionClass(PageApiController::class);
    $controller = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('json')->setValue($controller, new JsonResponder());
    $reflection->getProperty('documents')->setValue($controller, new DocumentNormalizer(new DocumentValidator()));

    $response = $reflection->getMethod('mutationForm')->invoke($controller, [
        'content_format' => 'block_json',
        'content_json' => $document,
    ], 1, null);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode())->toBe(422);
})->with([
    [['blocks' => [['type' => 'unsupported', 'data' => []]]]],
    [['schema_version' => 999, 'blocks' => []]],
    [['blocks' => null]],
]);