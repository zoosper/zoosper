<?php

declare(strict_types=1);

it('bounds the Site-scoped URL Rewrite collection and publishes canonical pagination metadata', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Api/UrlRewriteApiController.php');
    $repository = (string) file_get_contents($root . '/src/Repository/UrlRewriteRepository.php');
    expect($controller)
        ->toContain('pageForSite($site,Pager::fromQuery([')
        ->toContain("'pagination'=>\$this->pagination(\$result)")
        ->and($repository)
        ->toContain('public function pageForSite(int $siteId, Pager $requested): PaginationResult')
        ->toContain('SELECT COUNT(*) FROM url_rewrites WHERE site_id = :site_id')
        ->toContain('ORDER BY request_path ASC, id ASC LIMIT :limit OFFSET :offset');
});
