<?php

declare(strict_types=1);

it('requires PAT scope and owner permission while preserving Site isolation and structured content', function (): void {
    $source=(string)file_get_contents(dirname(__DIR__,3).'/src/Api/PageApiController.php');
    expect($source)->toContain("principal(\$request,'pages:read',true)")->toContain("can('page.view')")->toContain("can('page.manage')")
        ->toContain("'site_id'")->toContain("'content_json'")->toContain("'content_html'")
        ->toContain("page->siteId===\$request->siteContext()?->siteId")
        ->not->toContain('tokenHash')->not->toContain('authorization]');
});
