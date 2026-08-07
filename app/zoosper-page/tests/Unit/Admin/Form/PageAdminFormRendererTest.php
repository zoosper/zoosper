<?php

declare(strict_types=1);

it('keeps Page form SEO structured content and extensibility fields in its renderer', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Form/PageAdminFormRenderer.php');
    expect($source)->toContain("sectionsFor('page.form'")
        ->toContain("'contentJson'")
        ->toContain("'metaTitle'")
        ->toContain("'metaDescription'")
        ->toContain("'metaKeywords'")
        ->toContain("'canonicalUrl'")
        ->toContain("'publishChecked'")
        ->toContain('$this->csrf->token()');
});
