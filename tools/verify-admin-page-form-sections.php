<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin page form section verification\n";
print "============================================\n\n";

$sections = zoosper_phase0841_page_form_sections();
$html = zoosper_phase0841_render_page_form($sections);
$keys = array_map(
    static fn (\Zoosper\Admin\Form\AdminFormSection $section): string => $section->key,
    $sections,
);

$checks = [
    'page form uses sectioned class' => str_contains($html, 'page-form--sectioned'),
    'Page details section exists' => in_array('page.details', $keys, true) && str_contains($html, 'Page details'),
    'Content section exists' => in_array('page.content', $keys, true) && str_contains($html, 'Content'),
    'SEO section preserved' => in_array('page.seo', $keys, true) && str_contains($html, 'Search engine optimisation'),
    'Publishing section exists' => in_array('page.publishing', $keys, true) && str_contains($html, 'Publishing'),
    'content_json hidden field path preserved' => str_contains($html, 'name="content_json"'),
    'meta_title field preserved' => str_contains($html, 'name="meta_title"'),
    'meta_description field preserved' => str_contains($html, 'name="meta_description"'),
    'meta_keywords field preserved' => str_contains($html, 'name="meta_keywords"'),
    'canonical_url field preserved' => str_contains($html, 'name="canonical_url"'),
    'publish checkbox preserved' => str_contains($html, 'name="publish"'),
    'save action preserved' => str_contains($html, 'Save page'),
    'back action preserved' => str_contains($html, 'Back'),
    'sections sort predictably' => $keys === ['page.details', 'page.content', 'page.seo', 'page.publishing'],
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

/** @return list<\Zoosper\Admin\Form\AdminFormSection> */
function zoosper_phase0841_page_form_sections(): array
{
    return (new \Zoosper\Admin\Form\AdminFormProviderRegistry())
        ->add(new \Zoosper\Page\Admin\Form\PageDetailsSectionProvider())
        ->add(new \Zoosper\Page\Admin\Form\PageContentSectionProvider())
        ->add(new \Zoosper\Page\Admin\Form\PageSeoSectionProvider())
        ->add(new \Zoosper\Page\Admin\Form\PagePublishingSectionProvider())
        ->sectionsFor('page.form', zoosper_phase0841_page_form_context());
}

/** @param iterable<\Zoosper\Admin\Form\AdminFormSection> $sections */
function zoosper_phase0841_render_page_form(iterable $sections): string
{
    return (new \Zoosper\Admin\Form\AdminFormRenderer())->render('/admin/pages/edit?id=1', 'csrf-token', $sections);
}

/** @return array<string, mixed> */
function zoosper_phase0841_page_form_context(): array
{
    return [
        'siteOptions' => '<option value="1" selected>Main Website</option>',
        'title' => 'Home',
        'slug' => 'home',
        'editorHtml' => '<input type="hidden" name="content_json" value="{&quot;blocks&quot;:[]}"><textarea name="content"></textarea>',
        'contentJson' => '{&quot;blocks&quot;:[]}',
        'metaTitle' => 'Home',
        'metaDescription' => '',
        'metaKeywords' => '',
        'canonicalUrl' => '',
        'publishChecked' => ' checked',
        'backUrl' => '/admin/pages',
    ];
}
