<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\BulkAction;

use Zoosper\AdminGrid\BulkAction\GridBulkHostBindings;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpCoordinator;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpCoordinatorFactory;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorRegistry;
use Zoosper\Grid\BulkAction\GridBulkActionRegistry;
use Zoosper\Page\Admin\PageGridBulkActions;
use Zoosper\Page\Admin\PageGridWorkspace;
use Zoosper\Page\Repository\PageRepository;

/** Production backend composition for protected Page bulk actions. */
final readonly class PageBulkActionBackend
{
    public GridBulkHttpCoordinator $coordinator;

    public function __construct(
        PageRepository $pages,
        EventDispatcherInterface $events,
        AuditLoggerInterface $audit,
        GridBulkHostBindings $hostBindings,
    ) {
        $definitions = new GridBulkActionRegistry();
        foreach (PageGridBulkActions::serverDefinitions() as $definition) {
            $definitions->register(PageGridWorkspace::GRID_KEY, $definition);
        }

        $executors = new GridBulkActionExecutorRegistry();
        $executors->register(new PagePublishSelectedExecutor(
            $pages,
            new PagePublishSideEffects($events, $audit),
        ));

        $this->coordinator = (new GridBulkHttpCoordinatorFactory())->create(
            $definitions,
            $executors,
            $hostBindings,
        );
    }
}
