<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\Core\Form\AdminFormSection;
use Zoosper\Core\Form\AdminFormSectionProviderInterface;

/**
 * Provides publication controls and form actions for the page admin form.
 *
 * Phase 1.41 (page decoupling, part A): AdminFormSection and
 * AdminFormSectionProviderInterface now imported from Zoosper\Core\Form.
 * No logic changed.
 */
final readonly class PagePublishingSectionProvider implements AdminFormSectionProviderInterface
{
    public function formHandle(): string
    {
        return 'page.form';
    }

    public function sections(array $context): iterable
    {
        yield new AdminFormSection(
            key: 'page.publishing',
            title: 'Publishing',
            html: <<<HTML
        <label class="checkbox"><input type="checkbox" name="publish" value="1"{$context['publishChecked']}> Publish page</label>
        <div class="toolbar"><button type="submit">Save page</button><a class="button secondary" href="{$context['backUrl']}">Back</a></div>
HTML,
            sortOrder: 900,
            description: 'Control publication state and save your changes.',
            modifierClass: 'page-form__section--publishing',
        );
    }
}
