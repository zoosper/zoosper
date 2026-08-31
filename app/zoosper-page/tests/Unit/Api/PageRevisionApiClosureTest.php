<?php
declare(strict_types=1);
use Zoosper\Page\Api\PageApiController;
it('registers a stateless Site-scoped Page revision detail route', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-page/config/api_routes.php';
    $matches = array_values(array_filter($routes, static fn (array $route): bool => ($route['action'] ?? '') === 'revision'));
    expect($matches)->toHaveCount(1)
        ->and($matches[0]['method'])->toBe('GET')
        ->and($matches[0]['path'])->toBe('/api/v1/pages/{id:\d+}/revisions/{revisionId:\d+}')
        ->and($matches[0]['controller'])->toBe(PageApiController::class)
        ->and($matches[0]['public'])->toBeTrue()
        ->and($matches[0]['stateless'])->toBeTrue();
});
it('keeps revision detail inside the existing PAT permission and Page ownership boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-page/src/Api/PageApiController.php');
    expect($source)->toContain("public function revision(Request \$request): Response")
        ->toContain("principal(\$request, 'pages:read', true)")
        ->toContain('$this->revisions->revision($page->id, $revisionId)')
        ->toContain("'revision_not_found'")
        ->not->toContain('tokenHash');
});
it('bounds revision page size and preserves the established default', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-page/src/Api/PageApiController.php');
    expect($source)->toContain("query('page_size', '20')")
        ->toContain("'max_range' => 100")
        ->toContain('$requestedPageSize === false ? 20 : $requestedPageSize');
});










