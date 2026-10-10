<?php

declare(strict_types=1);

it('bounds the Site-scoped Page collection and publishes canonical pagination metadata', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Api/PageApiController.php');
    $repository = (string) file_get_contents($root . '/src/Repository/PageRepository.php');

    expect($controller)
        ->toContain('Pager::fromQuery([')
        ->toContain("'page' => \$request->query('page', '1')")
        ->toContain("'page_size' => \$request->query('page_size', '20')")
        ->toContain("'pages' => array_map(")
        ->toContain("'pagination' => \$this->normalisePagination(\$result)")
        ->and($repository)
        ->toContain('public function pageForSite(int $siteId, Pager $requested): PaginationResult')
        ->toContain('SELECT COUNT(*) FROM pages WHERE site_id = :site_id')
        ->toContain('LIMIT :limit OFFSET :offset')
        ->toContain("bindValue(':limit', \$pager->pageSize, PDO::PARAM_INT)")
        ->toContain("bindValue(':offset', \$pager->offset(), PDO::PARAM_INT)");
});
