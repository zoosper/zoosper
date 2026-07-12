<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Form;

use Zoosper\Admin\Form\AdminFormSection;
use Zoosper\Admin\Form\AdminFormSectionProviderInterface;

/**
 * Provides publication controls and form actions for the page admin form.
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
