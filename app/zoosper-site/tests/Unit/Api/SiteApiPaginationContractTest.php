<?php

declare(strict_types=1);

it('bounds the Site collection API and publishes canonical pagination metadata', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Api/SiteApiController.php');
    $repository = (string) file_get_contents($root . '/src/Repository/SiteRepository.php');
    expect($controller)
        ->toContain('pageForApi(Pager::fromQuery([')
        ->toContain("'pagination'=>\$this->pagination(\$result)")
        ->and($repository)
        ->toContain('public function pageForApi(Pager $requested): PaginationResult')
        ->toContain('SELECT COUNT(*) FROM sites')
        ->toContain('ORDER BY name ASC, id ASC LIMIT :limit OFFSET :offset');
});
