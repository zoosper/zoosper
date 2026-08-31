<?php

declare(strict_types=1);

use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Logger\Driver\LocalLogger;

function exceptionPresentationHandler(bool $debug): ErrorHandler
{
    return new ErrorHandler(
        new LocalLogger(sys_get_temp_dir() . '/zoosper-http-errors-' . bin2hex(random_bytes(4)) . '.log', true),
        $debug,
    );
}

it('renders Marko HTML for web exceptions when debug is enabled', function (): void {
    $response = exceptionPresentationHandler(true)->httpResponse(new RuntimeException('Deliberate development exception.'));

    expect($response->statusCode())->toBe(500)
        ->and($response->headers()['Content-Type'])->toBe('text/html; charset=utf-8')
        ->and($response->body())->toContain('RuntimeException')
        ->toContain('Deliberate development exception.');
});

it('keeps production web exceptions generic', function (): void {
    $response = exceptionPresentationHandler(false)->httpResponse(new RuntimeException('Private implementation detail.'));

    expect($response->statusCode())->toBe(500)
        ->and($response->body())->toContain('An unexpected error occurred.')
        ->not->toContain('Private implementation detail.')
        ->not->toContain('RuntimeException');
});

it('keeps API exceptions generic JSON even when debug is enabled', function (): void {
    $response = exceptionPresentationHandler(true)->httpResponse(new RuntimeException('Private API detail.'), true);

    expect($response->statusCode())->toBe(500)
        ->and($response->headers()['Content-Type'])->toBe('application/json; charset=utf-8')
        ->and($response->body())->toContain('internal_error')
        ->not->toContain('Private API detail.');
});

it('wires both caught HTTP boundaries to the same environment-aware presenter', function (): void {
    $root = dirname(__DIR__, 5);
    $router = (string) file_get_contents($root . '/app/zoosper-core/src/Routing/Router.php');
    $application = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Application.php');
    $factory = (string) file_get_contents($root . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php');

    expect($router)->toContain('$this->errorHandler?->httpResponse(')
        ->and($application)->toContain('$this->errorHandler?->logException(')
        ->toContain('$this->errorHandler?->httpResponse(')
        ->and($factory)->toContain("\$config->get('app.debug', false)")
        ->toContain('$errorHandler,');
});










