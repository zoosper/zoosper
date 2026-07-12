<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\Admin\Form\AdminFormSection;
use Zoosper\Admin\Form\AdminFormSectionProviderInterface;

/**
 * Provides the search engine optimisation fields for the page admin form.
 */
final readonly class PageSeoSectionProvider implements AdminFormSectionProviderInterface
{
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function sections(array $context): iterable
    {
        yield new AdminFormSection(
            key: 'page.seo',
            title: 'Search engine optimisation',
            html: <<<HTML
        <label>Meta title <input type="text" name="meta_title" value="{$context['metaTitle']}" maxlength="255"></label>
        <label>Meta description <textarea name="meta_description" rows="3" maxlength="500">{$context['metaDescription']}</textarea></label>
        <label>Meta keywords <input type="text" name="meta_keywords" value="{$context['metaKeywords']}" maxlength="500"></label>
        <label>Canonical URL <input type="url" name="canonical_url" value="{$context['canonicalUrl']}" maxlength="500"></label>
HTML,
            sortOrder: 300,
            description: 'Optional search metadata kept separate from the page body.',
            modifierClass: 'page-form__section--seo',
        );
    }
}
