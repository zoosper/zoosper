<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\AdminForm\AdminFormSection;
use Zoosper\AdminForm\AdminFormSectionProviderInterface;

/**
 * Provides the page body editor section.
 *
 * Phase 1.41 (page decoupling, part A): AdminFormSection and
 * AdminFormSectionProviderInterface now imported from Zoosper\AdminForm.
 * No logic changed.
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
            description: '',
            modifierClass: 'page-form__section--content',
        );
    }
}











