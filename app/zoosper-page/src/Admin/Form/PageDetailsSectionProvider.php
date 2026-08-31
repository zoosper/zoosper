<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\AdminForm\AdminFormSection;
use Zoosper\AdminForm\AdminFormSectionProviderInterface;

/**
 * Provides the core page identity fields for the page admin form.
 *
 * Phase 1.41 (page decoupling, part A): AdminFormSection and
 * AdminFormSectionProviderInterface now imported from Zoosper\AdminForm.
 * No logic changed.
 */
final readonly class PageDetailsSectionProvider implements AdminFormSectionProviderInterface
{
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function sections(array $context): iterable
    {
        yield new AdminFormSection(
            key: 'page.details',
            title: 'Page details',
            html: <<<HTML
        <label>Site <select name="site_id" required>{$context['siteOptions']}</select></label>
        <label>Title <input type="text" name="title" value="{$context['title']}" required></label>
        <label>Slug <input type="text" name="slug" value="{$context['slug']}" required></label>
HTML,
            sortOrder: 100,
            description: 'Choose the site and define the public page identity.',
            modifierClass: 'page-form__section--details',
        );
    }
}











