<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\Admin\Form\AdminFormSection;
use Zoosper\Admin\Form\AdminFormSectionProviderInterface;

/**
 * Provides the page body editor section.
 */
final readonly class PageContentSectionProvider implements AdminFormSectionProviderInterface
{
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function sections(array $context): iterable
    {
        yield new AdminFormSection(
            key: 'page.content',
            title: 'Content',
            html: (string) $context['editorHtml'],
            sortOrder: 200,
            description: 'Edit the page body. The HTML fallback is sanitised and Editor.js JSON is validated on save.',
            modifierClass: 'page-form__section--content',
        );
    }
}
