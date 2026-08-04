<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\BulkAction;

use InvalidArgumentException;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionExecutionResult;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorInterface;
use Zoosper\Grid\BulkAction\GridBulkExecutionContext;
use Zoosper\Grid\BulkAction\GridBulkSelection;
use Zoosper\Page\Admin\PageGridWorkspace;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;

/** Page-owned executor for the future protected Publish selected action. */
final readonly class PagePublishSelectedExecutor implements GridBulkActionExecutorInterface
{
    public const ACTION_ID = 'page.publish';

    public function __construct(
        private PageRepository $pages,
        private PagePublishSideEffectsInterface $sideEffects,
    ) {
    }

    public function gridKey(): string
    {
        return PageGridWorkspace::GRID_KEY;
    }

    public function actionId(): string
    {
        return self::ACTION_ID;
    }

    public function execute(
        GridBulkActionDefinition $definition,
        GridBulkSelection $selection,
        GridBulkExecutionContext $context,
    ): GridBulkActionExecutionResult {
        if ($definition->id !== self::ACTION_ID) {
            throw new InvalidArgumentException('Page publish executor received an unexpected action definition.');
        }

        $selectedCount = $selection->count();
        $pages = $this->preflight($selection);
        $publishable = array_values(array_filter(
            $pages,
            static fn (Page $page): bool => !$page->isPublished(),
        ));
        $skipped = $selectedCount - count($publishable);

        foreach ($publishable as $page) {
            $this->pages->publish($page->id, $context->actor->adminUserId);
            $this->sideEffects->afterPublished($page, $context, $selectedCount);
        }

        $published = count($publishable);
        $message = $published === 0
            ? sprintf('No Pages required publication. All %d selected Pages were already published.', $skipped)
            : sprintf(
                'Published %d %s.%s',
                $published,
                $published === 1 ? 'Page' : 'Pages',
                $skipped > 0 ? sprintf(' Skipped %d already published.', $skipped) : '',
            );

        return GridBulkActionExecutionResult::success($message, [
            'selected' => $selectedCount,
            'published' => $published,
            'skipped_already_published' => $skipped,
        ]);
    }

    /** @return list<Page> */
    private function preflight(GridBulkSelection $selection): array
    {
        $pages = [];
        foreach ($selection->identities as $identity) {
            $value = (string) $identity;
            if (!ctype_digit($value) || (int) $value < 1) {
                throw new InvalidArgumentException('Page bulk publication requires positive integer identities.');
            }
            $page = $this->pages->findById((int) $value);
            if ($page === null) {
                throw new InvalidArgumentException(sprintf('Selected Page %d was not found.', (int) $value));
            }
            $pages[] = $page;
        }

        return $pages;
    }
}
