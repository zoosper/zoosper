<?php

declare(strict_types=1);

it('bounds the Site-scoped Menu collection and publishes canonical pagination metadata', function (): void {
    $root=dirname(__DIR__,3);$controller=(string)file_get_contents($root.'/src/Api/MenuApiController.php');$repository=(string)file_get_contents($root.'/src/Repository/PdoMenuAdminRepository.php');
    expect($controller)->toContain('Pager::fromQuery(')->toContain("'pagination'=>\$this->normalisePagination(\$result)")->and($repository)->toContain('public function pageForSite(int $siteId, Pager $requested): PaginationResult')->toContain('SELECT COUNT(*) FROM menus WHERE site_id=:site')->toContain('LIMIT :limit OFFSET :offset');
});
